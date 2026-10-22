<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageOptimizer
{
    private const MAX_DIMENSION = 1600;

    private const WEBP_QUALITY = 82;

    /**
     * Lebar varian WebP tambahan untuk srcset (Lighthouse: uses-responsive-images).
     */
    private const RESPONSIVE_WIDTHS = [400, 800];

    public static function store(UploadedFile $file, string $directory): string
    {
        $image = (new ImageManager(new Driver))->read($file->getRealPath());
        $image->orient();
        $image->scaleDown(width: self::MAX_DIMENSION, height: self::MAX_DIMENSION);

        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $name = $file->hashName();
        $webpName = pathinfo($name, PATHINFO_FILENAME).'.webp';

        $contents = match ($extension) {
            'png' => $image->toPng()->toString(),
            'webp' => $image->toWebp(quality: self::WEBP_QUALITY)->toString(),
            default => $image->toJpeg(quality: self::WEBP_QUALITY)->toString(),
        };

        Storage::disk('public')->put($directory.'/'.$name, $contents);

        if ($extension !== 'webp') {
            Storage::disk('public')->put($directory.'/'.$webpName, $image->toWebp(quality: self::WEBP_QUALITY)->toString());
        }

        self::storeResponsiveVariants($file->getRealPath(), $directory, pathinfo($name, PATHINFO_FILENAME));

        return $directory.'/'.$name;
    }

    public static function delete(?string $relativePath): void
    {
        if (! $relativePath) {
            return;
        }

        $disk = Storage::disk('public');
        $disk->delete($relativePath);

        $webp = self::webpPath($relativePath);

        if ($disk->exists($webp)) {
            $disk->delete($webp);
        }

        foreach (self::RESPONSIVE_WIDTHS as $width) {
            $variant = self::responsivePath($relativePath, $width);

            if ($disk->exists($variant)) {
                $disk->delete($variant);
            }
        }
    }

    public static function webpPath(string $relativePath): string
    {
        return pathinfo($relativePath, PATHINFO_DIRNAME).'/'.pathinfo($relativePath, PATHINFO_FILENAME).'.webp';
    }

    /**
     * Jalur varian lebar tertentu, mis. products/foto-800w.webp.
     */
    public static function responsivePath(string $relativePath, int $width): string
    {
        return pathinfo($relativePath, PATHINFO_DIRNAME).'/'.pathinfo($relativePath, PATHINFO_FILENAME).'-'.$width.'w.webp';
    }

    public static function hasWebp(?string $relativePath): bool
    {
        if (! $relativePath) {
            return false;
        }

        return Storage::disk('public')->exists(self::webpPath($relativePath));
    }

    /**
     * @return array<int, string> lebar => jalur relatif (tanpa asset prefix)
     */
    public static function responsiveVariants(string $relativePath): array
    {
        $disk = Storage::disk('public');
        $variants = [];

        foreach (self::RESPONSIVE_WIDTHS as $width) {
            $path = self::responsivePath($relativePath, $width);

            if ($disk->exists($path)) {
                $variants[$width] = $path;
            }
        }

        return $variants;
    }

    private static function storeResponsiveVariants(string $sourcePath, string $directory, string $base): void
    {
        foreach (self::RESPONSIVE_WIDTHS as $width) {
            $image = (new ImageManager(new Driver))->read($sourcePath);

            if ($image->width() <= $width) {
                continue;
            }

            $image->scaleDown(width: $width);

            Storage::disk('public')->put(
                $directory.'/'.$base.'-'.$width.'w.webp',
                $image->toWebp(quality: self::WEBP_QUALITY)->toString()
            );
        }
    }
}
