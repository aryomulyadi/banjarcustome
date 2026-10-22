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

    public function test_color_filter_sidebar_shows_label_and_counts(): void
    {
        $this->createProduct('Kaos Hitam A', 'Kaos', ['Hitam']);
        $this->createProduct('Kaos Hitam B', 'Kaos', ['Hitam', 'Putih']);

        $response = $this->get('/produk');

        $response->assertOk()
            ->assertSee('Warna Tersedia')
            ->assertSee('Produk yang tersedia dalam warna pilihan.');

        $html = $response->getContent();

        $this->assertStringContainsString(
            'title="Hitam — 2 produk"',
            $html,
            'Swatch Hitam menampilkan jumlah produk di tooltip.'
        );
        $this->assertStringContainsString(
            'title="Putih — 1 produk"',
            $html,
            'Swatch Putih menampilkan jumlah produk di tooltip.'
        );
        $this->assertStringContainsString(
            'aria-label="Hitam, 2 produk"',
            $html,
            'Radio warna memiliki accessible name.'
        );
    }

    public function test_reset_button_keeps_current_path(): void
    {
        $this->createProduct('Kaos Dua Warna', 'Kaos Polos', ['Hitam']);

        $category = Category::where('slug', 'kaos-polos')->firstOrFail();

        $response = $this->get('/kategori/'.$category->slug.'?warna=Hitam');

        $response->assertOk();

        $html = $response->getContent();

        $this->assertMatchesRegularExpression(
            '/href="([^"]+)"[^>]*>\s*Reset\s*<\/a>/u',
            $html,
            'Tombol Reset dirender sebagai link.'
        );

        preg_match('/href="([^"]+)"[^>]*>\s*Reset\s*<\/a>/u', $html, $matches);

        $this->assertSame(
            url('/kategori/'.$category->slug),
            $matches[1] ?? null,
            'Reset tetap di path kategori saat ini tanpa query string (Fase 26).'
        );
    }

    public function test_product_detail_color_chips_link_to_filtered_catalog(): void
    {
        $product = $this->createProduct('Kemeja Seragam Dua Warna', 'Kemeja', ['Putih', 'Navy']);

        $response = $this->get('/produk/'.$product->slug);

        $response->assertOk()
            ->assertSee(route('produk.index', ['warna' => 'Putih']))
            ->assertSee(route('produk.index', ['warna' => 'Navy']))
            ->assertSee('Filter katalog, warna Putih')
            ->assertSee('Klik warna untuk melihat semua produk yang tersedia');
    }

    public function test_catalog_highlights_matching_color_swatch_when_filter_active(): void
    {
        $this->createProduct('Kaos Dua Warna', 'Kaos', ['Putih', 'Hitam']);

        $withoutFilter = $this->get('/produk');
        $withoutFilter->assertOk();
        $this->assertDoesNotMatchRegularExpression(
            '/border-card[^"]*ring-ring/',
            $withoutFilter->getContent(),
            'Tanpa filter, swatch tidak boleh di-highlight.'
        );

        $filtered = $this->get('/produk?warna=Hitam');
        $filtered->assertOk()->assertSee('Kaos Dua Warna');

        $html = $filtered->getContent();

        $this->assertMatchesRegularExpression(
            '/h-4 w-4 rounded-full border-2 border-card[^"]*ring-ring[^"]*"\s+style="[^"]*"\s+title="Hitam"/',
            $html,
            'Swatch Hitam mendapat ring highlight saat filter aktif.'
        );

        $this->assertLessThan(
            strpos($html, 'title="Putih"'),
            strpos($html, 'title="Hitam"'),
            'Swatch yang cocok didahulukan dalam daftar swatch kartu.'
        );
    }
}
