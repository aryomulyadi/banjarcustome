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
        'vpQQxv0WeTmbQgVl6yFv2j8CjfXFUAoVQNNhiSJY.png' => 'sablon-kaos-satuan',
        'zfKuZ4b3165RiHjUVlAkSxaQMucGB7Qts4cSZb1Z.png' => 'kaos-promosi-custom',
        '3m0TI6qYftj49MlRF1RhMtUkjb0BqV3OZd2jobCz.png' => 'kaos-lengan-panjang-polos',
        'dKApm4nvD0LhJsn49wAWX1zvhSNcOwKWCyiEKJuJ.png' => 'kaos-polos-cotton-combed-30s',
        '29bMjsoDin91VOQlVGI7m9qYYgg0SFjKjpuJ3yhk.png' => 'jersey-futsal-printing',
        '3wIQGoY09PC5zjDMflyeEJN2sVUDL7YnxrSL8l6O.png' => 'jersey-voli-tim',
        'Js5ZnJ7QtpCfdy7u37WjlXbQb8AGgQ1Gy9UD57N5.png' => 'totebag-kanvas-custom',
        's24xDswosjt3PBsceYmviJnufHA0mdzlKEylRrtP.png' => 'kemeja-kantor-seragam',
    ];

    private const GALLERY_MAP = [
        'jBPDilczsfuBVBHxiOe0YpYAh98zoj42cRIqZmAv.jpg' => 'Kaos Event Promosi',
        'HPw0nYnkvo0ikan49O5pD4Oh1PyGhXFKxAVvv4SW.jpg' => 'Jersey Futsal Komunitas Banjarmasin',
        'o6wJTfS7PWeOVXsySfMn6ngR82eLpC5KxBD3GtOt.jpg' => 'Merchandise Totebag Event',
        'xoKrFrhuHRsrdBPCjbGV0ztKZtiMADyfVAG4Mc1o.jpg' => 'Seragam Staf Lapangan',
        'dqwGkuWnBx0qq9qF0DxGvKo6VfghETS9SrLrT6OP.jpg' => 'Kaos Polos Grosir Komunitas',
        '24MT7B4GTBvwDkWEBxqhasp245ga2uE6uuln2nNb.jpg' => 'Jersey Basket Sekolah',
        'dIhe6W76yJiwMcnesqrImBezW2lvTIv66fFT878C.jpg' => 'Seragam Kantor Perusahaan',
        'VpJUFkUpXHxDUv7Wl7oG40KBxca5MyfnA1ZCMRUY.jpg' => 'Jaket Komunitas Motor',
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
