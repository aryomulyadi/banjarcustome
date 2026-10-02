<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CatalogTest extends TestCase
{
    use RefreshDatabase;

    private function createProduct(string $title, string $categoryName = 'Kaos Polos', array $colors = []): Product
    {
        $category = Category::firstOrCreate(
            ['slug' => Str::slug($categoryName)],
            ['name' => $categoryName]
        );

        $product = Product::create([
            'category_id' => $category->id,
            'title' => $title,
            'slug' => Str::slug($title),
            'description' => 'Deskripsi untuk '.$title,
            'price_estimate' => 'Mulai Rp 50.000',
        ]);

        foreach ($colors as $name) {
            ProductColor::create([
                'product_id' => $product->id,
                'name' => $name,
                'hex' => '#1C1C1C',
            ]);
        }

        return $product;
    }

    public function test_catalog_page_returns_successful_response(): void
    {
        $this->createProduct('Kaos Polos Hitam');

        $response = $this->get('/produk');

        $response->assertOk()->assertSee('Kaos Polos Hitam');
    }

    public function test_catalog_filters_by_color(): void
    {
        $this->createProduct('Kaos Hitam Khusus', 'Kaos Polos', ['Hitam']);
        $this->createProduct('Kaos Putih Khusus', 'Kaos Polos', ['Putih']);

        $response = $this->get('/produk?warna=Hitam');

        $response->assertOk()
            ->assertSee('Kaos Hitam Khusus')
            ->assertDontSee('Kaos Putih Khusus');
    }

    public function test_catalog_filters_by_category(): void
    {
        $this->createProduct('Jersey Futsal Tim', 'Jersey Printing');
        $this->createProduct('Jaket Komunitas', 'Jaket');

        $category = Category::where('slug', 'jersey-printing')->firstOrFail();

        $response = $this->get('/kategori/'.$category->slug);

        $response->assertOk()
            ->assertSee('Jersey Futsal Tim')
            ->assertDontSee('Jaket Komunitas');
    }

    public function test_product_detail_page_returns_successful_response(): void
    {
        $product = $this->createProduct('Kemeja Seragam Kantor', 'Kemeja', ['Putih']);

        $response = $this->get('/produk/'.$product->slug);

        $response->assertOk()
            ->assertSee('Kemeja Seragam Kantor')
            ->assertSee('Putih')
            ->assertSee('Pesan Produk Ini');
    }

    public function test_unknown_product_returns_404(): void
    {
        $this->createProduct('Produk Nyata');

        $this->get('/produk/tidak-ada')->assertNotFound();
    }

    public function test_catalog_search_returns_matching_product(): void
    {
        $this->createProduct('Jersey Basket Custom');
        $this->createProduct('Totebag Kanvas');

        $response = $this->get('/produk?q=jersey');

        $response->assertOk()
            ->assertSee('Jersey Basket Custom')
            ->assertDontSee('Totebag Kanvas');
    }
}
