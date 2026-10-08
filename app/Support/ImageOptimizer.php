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
    }

    public static function webpPath(string $relativePath): string
    {
        return pathinfo($relativePath, PATHINFO_DIRNAME).'/'.pathinfo($relativePath, PATHINFO_FILENAME).'.webp';
    }

    public static function hasWebp(?string $relativePath): bool
    {
        if (! $relativePath) {
            return false;
        }

        return Storage::disk('public')->exists(self::webpPath($relativePath));
    }
}
