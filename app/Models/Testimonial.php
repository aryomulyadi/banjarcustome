<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Testimonial extends Model
{
    protected $fillable = [
        'name',
        'role',
        'city',
        'rating',
        'photo',
        'content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'rating' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->latest()->orderByDesc('id');
    }

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget('home.testimonials');

        static::saved($forget);
        static::deleted($forget);
    }
}
