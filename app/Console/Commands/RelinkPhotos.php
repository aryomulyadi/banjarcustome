<?php

namespace App\Console\Commands;

use App\Models\Gallery;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RelinkPhotos extends Command
{
    protected $signature = 'photos:relink
        {--dry-run : Tampilkan pasangan tanpa menulis database}';

    protected $description = 'Menautkan kembali file foto lama di storage ke produk/galeri yang kolom image-nya kosong';

    private const PRODUCT_MAP = [
        'dXtAXW1n3Miqw00lFcnY9q7sZbVqU0TkjqOKPh3G.png' => 'kemeja-kantor-seragam',
        'nIQfs5VVj4xZ9rcvUutylC3F2mhm7q8e86TW8QMy.png' => 'tote-bag-kanvas-premium',
        'tE9YjXYCPMrjJG9swApq5GCUH2Vr90fO7FszJytk.png' => 'tumbler-custom',
    ];

    private const GALLERY_MAP = [
        'OavS5V8xaJKdQSxxFyo0HzapRIugurm6ePYwHLfa.png' => 'Jersey Futsal',
        'HuJXj6tpIewc06vYhFLlVLJI1XgDB1Gi8RaJxtpK.jpg' => 'Seragam Kantor Perusahaan',
        'SXJNO9YBYqUsYU6bWMoY6EoN06JB5JSXP0lB74Tc.jpg' => 'Kaos Panjang Gathering',
        'DFFwfWVtiPyMHo4YhrMg8f4GEaaTspzew6fAoXL8.png' => 'Jersey Komunitas Motor',
        'A98kr2Lj6QmreSiOG9faCxEOxof4jKqSxHOCMWzU.png' => 'Jersey Basket Sekolah',
        'SoUdE05EKFKIQ7MMUOLSzj7ECH7TGTZG505166sV.jpg' => 'Merchandise Goodiebag Event',
        'GHu0mWy8EexLRVniLoJsCFcAXnzvZlnbYhG1ZOuY.jpg' => 'Kaos Event',
        'ZYGfXDLPX9jqBFbsV8ZatrzlrD4dkw1TCHqIsufv.jpg' => 'Tumbler',
    ];

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');

        $this->process('products', self::PRODUCT_MAP, Product::class, 'slug', $dryRun);
        $this->process('galleries', self::GALLERY_MAP, Gallery::class, 'title', $dryRun);

        $this->warnOrphans('products', self::PRODUCT_MAP, Product::class, 'slug', 'image');
        $this->warnOrphans('galleries', self::GALLERY_MAP, Gallery::class, 'title', 'image');

        $this->newLine();
        $this->info($dryRun
            ? 'Dry-run selesai — tidak ada data yang diubah. Jalankan "php artisan photos:relink" untuk menulis.'
            : 'Selesai. Periksa katalog & galeri di admin; perbaiki pasangan yang meleset lewat form edit.');

        return self::SUCCESS;
    }

    private function process(string $dir, array $map, string $modelClass, string $targetColumn, bool $dryRun): void
    {
        $this->newLine();
        $this->line(ucfirst($dir).':');

        $rows = [];
        $linked = 0;
        $skipped = 0;

        foreach ($map as $file => $targetValue) {
            $relative = $dir.'/'.$file;
            $model = $modelClass::where($targetColumn, $targetValue)->first();
            $fileExists = Storage::disk('public')->exists($relative);

            if (! $model || ! $fileExists) {
                $rows[] = [$file, (string) $targetValue, ! $model ? 'TARGET TIDAK ADA' : 'FILE HILANG'];

                continue;
            }

            if ($model->image) {
                $skipped++;
                $rows[] = [$file, $targetValue, 'sudah ada foto, dilewati'];

                continue;
            }

            if ($dryRun) {
                $rows[] = [$file, $targetValue, 'siap ditautkan'];
            } else {
                $model->forceFill(['image' => $relative])->save();
                $linked++;
                $rows[] = [$file, $targetValue, 'ditautkan'];
            }
        }

        $this->table(['File', 'Target', 'Status'], $rows);

        $this->components->info(sprintf(
            '%s: %d ditautkan, %d dilewati%s.',
            ucfirst($dir),
            $linked,
            $skipped,
            $dryRun ? ' (dry-run)' : ''
        ));
    }

    private function warnOrphans(string $dir, array $map, string $modelClass, string $targetColumn, string $imageColumn): void
    {
        $files = collect(Storage::disk('public')->files($dir))
            ->map(fn (string $path) => basename($path))
            ->reject(fn (string $file) => array_key_exists($file, $map))
            ->values();

        if ($files->isNotEmpty()) {
            $this->warn(ucfirst($dir).' - file tanpa pasangan di peta: '.$files->implode(', '));
        }

        $unmatched = $modelClass::query()
            ->whereNull($imageColumn)
            ->get()
            ->reject(fn ($model) => in_array($model->getAttribute($targetColumn), array_values($map), true))
            ->pluck($targetColumn)
            ->all();

        if ($unmatched !== []) {
            $this->warn(ucfirst($dir).' - masih tanpa foto: '.implode(', ', $unmatched));
        }
    }
}
