<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use App\Support\CatalogData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class CatalogSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_sync_creates_real_catalog_with_correct_prices(): void
    {
        Artisan::call('catalog:sync');

        $this->assertSame(15, Product::count(), 'Katalog final berisi 15 produk (kemeja koko hanya ikut bila sudah ada).');
        $this->assertSame(8, Category::count());

        $expected = [
            'kaos-cotton-combed-24s' => ['Kaos Cotton Combed 24s', 'Kaos', 'Mulai Rp 75.000', 75000],
            'kaos-dewasa-cotton-combed-30s' => ['Kaos Dewasa Cotton Combed 30s', 'Kaos', 'Mulai Rp 65.000', 65000],
            'kaos-anak-cotton-combed-30s' => ['Kaos Anak Cotton Combed 30s', 'Kaos', 'Mulai Rp 45.000', 45000],
            'polo-shirt-kerah' => ['Polo Shirt Kerah', 'Polo', 'Mulai Rp 70.000', 70000],
            'topi-twill-drill-jaring-trucker' => ['Topi Twill Drill / Jaring / Trucker', 'Topi', 'Mulai Rp 15.000', 15000],
            'topi-american-drill-ribstop' => ['Topi American Drill / Ribstop', 'Topi', 'Mulai Rp 25.000', 25000],
            'topi-raphel-polyester-denim-sueding' => ['Topi Raphel (Polyester, Denim, Sueding)', 'Topi', 'Mulai Rp 26.000', 26000],
            'topi-canvas-laken' => ['Topi Canvas / Laken', 'Topi', 'Mulai Rp 30.000', 30000],
            'goodie-bag-spunbond' => ['Goodie Bag Spunbond', 'Merchandise', 'Mulai Rp 3.000', 3000],
            'tote-bag-blacu' => ['Tote Bag Blacu', 'Merchandise', 'Mulai Rp 10.000', 10000],
            'tote-bag-kanvas-premium' => ['Tote Bag Kanvas Premium', 'Merchandise', 'Mulai Rp 35.000', 35000],
            'tumbler-custom' => ['Tumbler Custom', 'Tumbler', 'Mulai Rp 35.000', 35000],
            'kemeja-kantor-seragam' => ['Kemeja Kantor Seragam', 'Kemeja', 'Mulai Rp 95.000', 95000],
            'jaket-coach-jacket' => ['Jaket Coach Jacket', 'Jaket', 'Mulai Rp 135.000', 135000],
            'jaket-hoodie-zipper' => ['Jaket Hoodie Zipper', 'Jaket', 'Mulai Rp 150.000', 150000],
        ];

        foreach ($expected as $slug => [$title, $category, $estimate, $base]) {
            $product = Product::where('slug', $slug)->first();

            $this->assertNotNull($product, "Produk {$slug} harus ada.");
            $this->assertSame($title, $product->title);
            $this->assertSame($category, $product->category?->name, "Produk {$slug} di kategori {$category}.");
            $this->assertSame($estimate, $product->price_estimate);
            $this->assertSame($base, $product->base_price);
        }

        $this->assertSame(7, Product::where('slug', 'kaos-cotton-combed-24s')->first()->colors()->count());
        $this->assertSame(38, Product::where('slug', 'kaos-dewasa-cotton-combed-30s')->first()->colors()->count());
        $this->assertSame(38, Product::where('slug', 'polo-shirt-kerah')->first()->colors()->count());

        $this->assertSame(
            [['min_qty' => 12, 'unit_price' => 75000]],
            Product::where('slug', 'kaos-cotton-combed-24s')->first()->price_tiers
        );
    }

    public function test_catalog_sync_cleans_legacy_data_and_fixes_category_slugs(): void
    {
        $tumbler = Category::create(['name' => 'Tumbler', 'slug' => 'sablon']);
        $kaosOld = Category::create(['name' => 'Jaket', 'slug' => 'kaos-polos']);
        $jersey = Category::create(['name' => 'Kaos', 'slug' => 'jersey-printing']);
        $jaket = Category::create(['name' => 'Jersey', 'slug' => 'jaket']);
        $kemejaA = Category::create(['name' => 'Kemeja', 'slug' => 'merchandise']);
        Category::create(['name' => 'Kemeja', 'slug' => 'kemeja-2']);
        $kemejaB = Category::create(['name' => 'Kemeja', 'slug' => 'kemeja']);
        Category::create(['name' => 'Topi', 'slug' => 'topi']);

        Product::create(['category_id' => $tumbler->id, 'title' => 'Kaos Promosi Custom', 'slug' => 'kaos-promosi-custom', 'price_estimate' => 'Mulai Rp 45.000']);
        Product::create(['category_id' => $kaosOld->id, 'title' => 'Kaos Polos Cotton Combed 30s', 'slug' => 'kaos-polos-cotton-combed-30s', 'price_estimate' => 'Mulai Rp 38.000']);
        Product::create(['category_id' => $jaket->id, 'title' => 'Jersey Futsal Printing', 'slug' => 'jersey-futsal-printing', 'price_estimate' => 'Mulai Rp 85.000']);
        Product::create(['category_id' => $kemejaA->id, 'title' => 'Totebag Kanvas Custom', 'slug' => 'totebag-kanvas-custom', 'price_estimate' => 'Mulai Rp 35.000']);
        $koko = Product::create(['category_id' => $kemejaA->id, 'title' => 'Kemeja Koko Kombinasi', 'slug' => 'kemeja-koko-kombinasi', 'price_estimate' => 'Mulai Rp 85.000']);

        Artisan::call('catalog:sync');

        foreach (['kaos-promosi-custom', 'kaos-polos-cotton-combed-30s', 'jersey-futsal-printing', 'totebag-kanvas-custom'] as $slug) {
            $this->assertNull(Product::where('slug', $slug)->first(), "Produk lama {$slug} harus terhapus.");
        }

        $this->assertNotNull(Product::where('slug', 'kemeja-koko-kombinasi')->first());
        $this->assertSame('Kemeja', $koko->fresh()->category?->name);

        $this->assertSame(8, Category::count(), 'Kategori duplikat/kosong dibersihkan.');
        $this->assertSame(1, Category::where('name', 'Kemeja')->count());

        foreach (CatalogData::categories() as $name => $meta) {
            $row = Category::where('name', $name)->first();

            $this->assertNotNull($row, "Kategori {$name} harus ada.");
            $this->assertSame($meta['slug'], $row->slug, "Slug kategori {$name} harus sinkron.");
        }

        $this->assertSame(0, Product::whereHas('category', fn ($q) => $q->where('name', 'Jersey'))->count());
    }

    public function test_catalog_sync_is_idempotent(): void
    {
        Artisan::call('catalog:sync');

        $before = [
            'products' => Product::count(),
            'categories' => Category::count(),
            'colors' => ProductColor::count(),
        ];

        Artisan::call('catalog:sync');

        $this->assertSame($before['products'], Product::count());
        $this->assertSame($before['categories'], Category::count());
        $this->assertSame($before['colors'], ProductColor::count());
    }
}
