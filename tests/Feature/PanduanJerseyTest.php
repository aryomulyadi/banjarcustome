<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PanduanJerseyTest extends TestCase
{
    use RefreshDatabase;

    public function test_panduan_jersey_page_shows_pedoman(): void
    {
        $this->get(route('panduan-jersey'))
            ->assertOk()
            ->assertSee('Panduan Jersey Custom')
            ->assertSee('Rp 85.000')
            ->assertSee('12 pcs')
            ->assertSee('14-25 hari kerja')
            ->assertSee('VIP ORDER')
            ->assertSee('Rp 10.000')
            ->assertSee('DP 50%')
            ->assertSee('Rp 50.000')
            ->assertSee('revisi bebas hingga fix')
            ->assertSee('Lengan Pendek')
            ->assertSee('Kerah O-Neck')
            ->assertSee('Bahan Dryfit Milano');
    }

    public function test_panduan_jersey_lists_collar_and_fabric_upgrades(): void
    {
        $this->get(route('panduan-jersey'))
            ->assertOk()
            ->assertSee('Upgrade Kerah (Collar)')
            ->assertSee('Upgrade Bahan (Fabric)')
            ->assertSee('V-Neck Tumpuk')
            ->assertSee('O-Neck Variasi')
            ->assertSee('Wangky Adidas Insert')
            ->assertSee('Shanghai Zipper')
            ->assertSee('Brazil Bintik')
            ->assertSee('Embos Geomatric')
            ->assertSee('Jaqcuard Moving')
            ->assertSee('Contoh Perhitungan')
            ->assertSee('Isi Form Pesanan');
    }

    public function test_panduan_jersey_is_listed_in_sitemap(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('panduan-jersey'), false);
    }

    public function test_jersey_product_links_to_panduan(): void
    {
        $category = Category::create(['name' => 'Jersey', 'slug' => 'jersey']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Jersey Futsal Custom',
            'slug' => 'jersey-futsal-custom',
        ]);

        $this->get(route('produk.show', $product->slug))
            ->assertOk()
            ->assertSee(route('panduan-jersey'), false)
            ->assertSee('Panduan Jersey 2026-2027');

        $otherCategory = Category::create(['name' => 'Kaos', 'slug' => 'kaos']);
        $otherProduct = Product::create([
            'category_id' => $otherCategory->id,
            'title' => 'Kaos Polos Combed',
            'slug' => 'kaos-polos-combed',
        ]);

        $this->get(route('produk.show', $otherProduct->slug))
            ->assertOk()
            ->assertDontSee('Panduan Jersey 2026-2027');
    }
}
