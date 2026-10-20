<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\MasterOption;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KatalogJerseyTest extends TestCase
{
    use RefreshDatabase;

    public function test_katalog_jersey_page_shows_pedoman(): void
    {
        $this->get(route('katalog-jersey'))
            ->assertOk()
            ->assertSee('Katalog Jersey Custom')
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

    public function test_katalog_jersey_lists_collar_and_fabric_upgrades(): void
    {
        $this->get(route('katalog-jersey'))
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
            ->assertSee('Standar (Rp 0)')
            ->assertSee('Contoh Perhitungan')
            ->assertSee('Isi Form Pesanan');
    }

    public function test_katalog_jersey_is_listed_in_sitemap(): void
    {
        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee(route('katalog-jersey'), false);
    }

    public function test_old_panduan_jersey_url_redirects_to_katalog(): void
    {
        $this->get('/panduan-jersey')
            ->assertRedirect('/katalog-jersey');
    }

    public function test_jersey_product_links_to_katalog(): void
    {
        $category = Category::create(['name' => 'Jersey', 'slug' => 'jersey']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Jersey Futsal Custom',
            'slug' => 'jersey-futsal-custom',
        ]);

        $this->get(route('produk.show', $product->slug))
            ->assertOk()
            ->assertSee(route('katalog-jersey'), false)
            ->assertSee('Lihat Katalog Jersey');

        $otherCategory = Category::create(['name' => 'Kaos', 'slug' => 'kaos']);
        $otherProduct = Product::create([
            'category_id' => $otherCategory->id,
            'title' => 'Kaos Polos Combed',
            'slug' => 'kaos-polos-combed',
        ]);

        $this->get(route('produk.show', $otherProduct->slug))
            ->assertOk()
            ->assertDontSee('Lihat Katalog Jersey');
    }

    public function test_katalog_shows_jersey_products_grid(): void
    {
        $category = Category::create(['name' => 'Jersey Futsal', 'slug' => 'jersey-futsal']);
        Product::create([
            'category_id' => $category->id,
            'title' => 'Jersey Basket Premium',
            'slug' => 'jersey-basket-premium',
        ]);

        $this->get(route('katalog-jersey'))
            ->assertOk()
            ->assertSee('Produk Jersey')
            ->assertSee('Jersey Basket Premium');
    }

    public function test_katalog_hides_product_grid_without_jersey_products(): void
    {
        $category = Category::create(['name' => 'Kaos', 'slug' => 'kaos']);
        Product::create([
            'category_id' => $category->id,
            'title' => 'Kaos Polos Combed',
            'slug' => 'kaos-polos-combed',
        ]);

        $this->get(route('katalog-jersey'))
            ->assertOk()
            ->assertDontSee('Produk Jersey')
            ->assertDontSee('Kaos Polos Combed');
    }

    public function test_katalog_option_grid_shows_photo_fee_and_hides_inactive(): void
    {
        MasterOption::create([
            'kind' => MasterOption::KIND_COLLAR,
            'name' => 'Wangky Rantai',
            'fee' => 7000,
            'image' => 'jersey-options/wangky.webp',
            'active' => true,
            'sort' => 5,
        ]);
        MasterOption::create([
            'kind' => MasterOption::KIND_COLLAR,
            'name' => 'Kerah Nonaktif',
            'fee' => 0,
            'active' => false,
        ]);

        $response = $this->get(route('katalog-jersey'));

        $response->assertOk()
            ->assertSee('Wangky Rantai')
            ->assertSee('+7.000')
            ->assertSee('storage/jersey-options/wangky.webp', false)
            ->assertDontSee('Kerah Nonaktif');

        // Kerah terisi di database => opsi config tidak dipakai (fallback per jenis).
        $response->assertDontSee('V-Neck Tumpuk');

        // Bahan masih kosong => fallback config tetap tampil.
        $response->assertSee('Brazil Bintik');
    }

    public function test_katalog_jersey_entry_is_promoted_in_header_and_footer(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee(route('katalog-jersey'), false);
        $this->assertSame(3, substr_count($response->getContent(), route('katalog-jersey')));
    }

    public function test_katalog_jersey_shows_faq_section(): void
    {
        $this->get(route('katalog-jersey'))
            ->assertOk()
            ->assertSee('Tanya Jawab Jersey')
            ->assertSee('FAQ umum')
            ->assertSee('Berapa minimal pemesanan jersey?')
            ->assertSee('Berapa lama proses produksi jersey?')
            ->assertSee('Bagaimana ketentuan pembayarannya?')
            ->assertSee('Berapa biaya desain dan berapa kali revisi?')
            ->assertSee('Apa perbedaan opsi kerah dan bahan?')
            ->assertSee('Bagaimana cara memesan jersey?');
    }
}
