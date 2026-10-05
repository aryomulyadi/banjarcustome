<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, $default = null)
    {
        $settings = Cache::remember('settings.all', now()->addMinutes(10), function () {
            return static::query()->pluck('value', 'key')->all();
        });

        return $settings[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget('settings.all');
    }

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget('settings.all');

        static::saved($forget);
        static::deleted($forget);
    }
}
