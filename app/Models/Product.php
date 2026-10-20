<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'description',
        'price_estimate',
        'base_price',
        'price_tiers',
        'image',
    ];

    protected $casts = [
        'price_tiers' => 'array',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort')->orderBy('id');
    }

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget('home.products');

        static::saved($forget);
        static::deleted($forget);
    }
}
