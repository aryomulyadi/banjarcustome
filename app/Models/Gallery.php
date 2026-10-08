<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Gallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'caption',
        'image',
        'category',
    ];

    protected static function booted(): void
    {
        $forget = function (): void {
            Cache::forget('home.galleries');
            Cache::forget('galleries.categories');
        };

        static::saved($forget);
        static::deleted($forget);
    }
}
