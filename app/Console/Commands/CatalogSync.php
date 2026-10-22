<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Support\CatalogData;
use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CatalogSync extends Command
{
    protected $signature = 'catalog:sync {--dry-run : Tampilkan rencana tanpa mengubah database}';

    protected $description = 'Sinkronkan kategori & produk ke katalog asli Banjar Custome (idempoten)';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $targets = CatalogData::categories();

        $deleted = $this->deleteReplacedProducts($dry);
        $categories = $this->syncCategories($targets, $dry);
        $this->cleanupCategories($targets, $dry);
        [$created, $updated] = $this->syncProducts($dry);
        $reassigned = $this->reassignKeptProducts($dry);

        Cache::forget('home.products');
        Cache::forget('nav.categories');

        $mode = $dry ? '[dry-run] ' : '';
        $this->info(sprintf(
            '%sProduk: %d baru, %d diperbarui, %d dihapus. Kategori: %d sinkron. Produk dipindah: %d.',
            $mode,
            $created,
            $updated,
            $deleted,
            $categories,
            $reassigned,
        ));

        return self::SUCCESS;
    }

    private function deleteReplacedProducts(bool $dry): int
    {
        $count = 0;

        foreach (CatalogData::replaceSlugs() as $slug) {
            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                continue;
            }

            if (! $dry) {
                if ($product->image) {
                    ImageOptimizer::delete($product->image);
                }

                foreach ($product->images as $image) {
                    ImageOptimizer::delete($image->image);
                }

                $product->delete();
            }

            $count++;
        }

        return $count;
    }

    /**
     * Sinkronkan kategori final: match by name, slug bebas dari blocker (tmp-{id}).
     *
     * @return int jumlah kategori yang dibuat/diubah
     */
    private function syncCategories(array $targets, bool $dry): int
    {
        $targetSlugs = array_column($targets, 'slug');
        $changed = 0;

        // Fase A: baris yang memegang slug target tetapi bukan pemilik name-nya -> tmp.
        Category::whereIn('slug', $targetSlugs)
            ->get()
            ->each(function (Category $row) use ($targets, $dry) {
                $owner = array_search($row->slug, array_column($targets, 'slug'), true);
                $ownerName = $owner === false ? null : array_keys($targets)[$owner];

                if ($ownerName !== null && $row->name !== $ownerName && ! $dry) {
                    $row->update(['slug' => 'tmp-'.$row->id]);
                }
            });

        // Fase B: assign final per name (keeper = yang sudah memegang slug target, else id terkecil).
        foreach ($targets as $name => $meta) {
            $rows = Category::where('name', $name)->orderBy('id')->get();
            $keeper = $rows->firstWhere('slug', $meta['slug']) ?? $rows->first();

            if (! $keeper) {
                if (! $dry) {
                    Category::create([
                        'name' => $name,
                        'slug' => $meta['slug'],
                        'description' => $meta['description'],
                    ]);
                }

                $changed++;

                continue;
            }

            if ($keeper->slug !== $meta['slug'] || $keeper->description !== $meta['description']) {
                if (! $dry) {
                    $keeper->update([
                        'slug' => $this->freeSlug($meta['slug'], $keeper->id),
                        'description' => $meta['description'],
                    ]);
                }

                $changed++;
            }
        }

        return $changed;
    }

    /**
     * Hapus kategori di luar target (duplikat/tmp) — pindahkan produknya dulu bila ada.
     */
    private function cleanupCategories(array $targets, bool $dry): void
    {
        $targetSlugs = array_column($targets, 'slug');
        $targetNames = array_keys($targets);

        Category::whereNotIn('slug', $targetSlugs)
            ->orderBy('id')
            ->get()
            ->each(function (Category $row) use ($targets, $targetNames, $dry) {
                if ($row->products()->exists()) {
                    $keeper = in_array($row->name, $targetNames, true)
                        ? Category::where('name', $row->name)->whereIn('slug', array_column($targets, 'slug'))->first()
                        : null;

                    if ($keeper && ! $dry) {
                        $row->products()->update(['category_id' => $keeper->id]);
                    } elseif ($keeper) {
                        // dry-run: tidak ada tulisan, cukup lewati.
                    } else {
                        $this->warn('Kategori "'.$row->name.'" (slug '.$row->slug.') masih berisi produk dan tidak punya padanan target — tidak dihapus.');

                        return;
                    }
                }

                if (! $dry) {
                    $row->delete();
                }
            });
    }

    /**
     * @return array{0: int, 1: int} [baru, diperbarui]
     */
    private function syncProducts(bool $dry): array
    {
        $created = 0;
        $updated = 0;

        foreach (CatalogData::products() as $slug => $data) {
            $category = Category::where('name', $data['category'])->first();

            if (! $category) {
                $this->error('Kategori "'.$data['category'].'" tidak ditemukan untuk produk '.$slug.'.');

                continue;
            }

            $tiers = $data['moq_lusin']
                ? [['min_qty' => 12, 'unit_price' => $data['base_price']]]
                : null;

            $attributes = [
                'category_id' => $category->id,
                'title' => $data['title'],
                'description' => $data['description'],
                'price_estimate' => $data['price_estimate'],
                'base_price' => $data['base_price'],
                'price_tiers' => $tiers,
            ];

            $product = Product::where('slug', $slug)->first();

            if (! $product) {
                if (! $dry) {
                    $product = Product::create($attributes + ['slug' => $slug]);
                    $this->syncColors($product, $data['colors']);
                }

                $created++;

                continue;
            }

            $dirty = false;

            foreach ($attributes as $key => $value) {
                if ($product->{$key} != $value) {
                    $dirty = true;

                    break;
                }
            }

            if ($dirty) {
                if (! $dry) {
                    $product->update($attributes);
                }

                $updated++;
            }

            if (! $dry) {
                $this->syncColors($product, $data['colors']);
            }
        }

        return [$created, $updated];
    }

    private function syncColors(Product $product, array $names): void
    {
        $palette = CatalogData::colorPalette();

        foreach ($names as $name) {
            $product->colors()->updateOrCreate(
                ['name' => $name],
                ['hex' => $palette[$name] ?? '#6B7280'],
            );
        }

        $product->colors()->whereNotIn('name', $names)->delete();
    }

    private function reassignKeptProducts(bool $dry): int
    {
        $count = 0;

        foreach (CatalogData::reassign() as $slug => $categoryName) {
            $product = Product::where('slug', $slug)->first();
            $category = Category::where('name', $categoryName)->first();

            if (! $product || ! $category || $product->category_id === $category->id) {
                continue;
            }

            if (! $dry) {
                $product->update(['category_id' => $category->id]);
            }

            $count++;
        }

        return $count;
    }

    private function freeSlug(string $base, int $ignoreId): string
    {
        if (! Category::where('slug', $base)->where('id', '!=', $ignoreId)->exists()) {
            return $base;
        }

        $slug = $base;
        $suffix = 2;

        while (Category::where('slug', $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
