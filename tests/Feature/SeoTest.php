<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Faq;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_has_meta_description_and_open_graph(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="description" content="', false)
            ->assertSee('<meta property="og:title"', false)
            ->assertSee('<link rel="canonical"', false)
            ->assertDontSee('<meta name="robots" content="noindex', false);
    }

    public function test_order_form_is_noindex(): void
    {
        $this->get('/pesan')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);

        $this->get('/cek-pesanan')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_login_page_is_noindex(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('<meta name="robots" content="noindex, nofollow">', false);
    }

    public function test_product_detail_has_product_json_ld(): void
    {
        $category = Category::create(['name' => 'Kaos', 'slug' => 'kaos']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Kaos Sablon Satuan',
            'slug' => 'kaos-sablon-satuan',
            'description' => 'Kaos sablon berkualitas.',
        ]);

        $response = $this->get(route('produk.show', $product->slug));

        $response->assertOk()
            ->assertSee('<script type="application/ld+json">', false)
            ->assertSee('"@type":"Product"', false)
            ->assertSee('"name":"Kaos Sablon Satuan"', false);
    }

    public function test_faq_page_has_faq_json_ld_with_active_entries_only(): void
    {
        Faq::create([
            'question' => 'Berapa lama produksi?',
            'answer' => 'Biasanya 5-7 hari kerja.',
            'position' => 1,
            'is_active' => true,
        ]);
        Faq::create([
            'question' => 'FAQ nonaktif?',
            'answer' => 'Tidak boleh tampil di halaman publik.',
            'position' => 2,
            'is_active' => false,
        ]);

        $response = $this->get('/faq');

        $response->assertOk()
            ->assertSee('"@type":"FAQPage"', false)
            ->assertSee('Berapa lama produksi?', false)
            ->assertDontSee('FAQ nonaktif?', false);
    }

    public function test_lokasi_page_has_local_business_json_ld(): void
    {
        $response = $this->get('/lokasi');

        $response->assertOk()
            ->assertSee('"@type":"LocalBusiness"', false)
            ->assertSee('Banjarmasin', false)
            ->assertSee('"openingHours":["Mo-Sa 09:00-17:00"]', false)
            ->assertSee('"@type":"GeoCoordinates"', false)
            ->assertSee('"latitude":-3.3199293', false)
            ->assertSee('"hasMap":"https://www.google.com/maps/place/', false);
    }

    public function test_lokasi_page_embeds_coordinates_and_links_to_google_maps(): void
    {
        $this->get('/lokasi')
            ->assertOk()
            ->assertSee('-3.3199293,114.6154141', false)
            ->assertSee(config('banjarcustom.maps_place_url'))
            ->assertSee('Buka di Google Maps')
            ->assertSee('Rute petunjuk arah');
    }

    public function test_lokasi_page_shows_opening_hours(): void
    {
        $this->get('/lokasi')
            ->assertOk()
            ->assertSee('09.00 – 17.00 WITA')
            ->assertSee('Tutup')
            ->assertSee('Minggu');
    }

    public function test_robots_txt_blocks_private_routes_and_points_to_sitemap(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Disallow: /admin', false)
            ->assertSee('Disallow: /pesan', false)
            ->assertSee('Sitemap: '.route('sitemap'), false);
    }

    public function test_sitemap_lists_static_dynamic_urls(): void
    {
        $category = Category::create(['name' => 'Jersey', 'slug' => 'jersey']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Jersey Printing',
            'slug' => 'jersey-printing-tes',
        ]);

        $response = $this->get('/sitemap.xml');

        $response->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('home'), false)
            ->assertSee(route('layanan'), false)
            ->assertSee(route('kategori', $category->slug), false)
            ->assertSee(route('produk.show', $product->slug), false)
            ->assertDontSee(route('admin.dashboard'), false)
            ->assertDontSee(route('pesan.create'), false);
    }

    public function test_unknown_url_renders_custom_404_page(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('404', false);
    }

    public function test_security_headers_are_set_on_responses(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy');
    }

    public function test_content_pages_return_successful_response(): void
    {
        foreach (['/tentang', '/layanan', '/lokasi', '/faq', '/panduan-ukuran', '/kebijakan-privasi', '/syarat-ketentuan'] as $uri) {
            $this->get($uri)->assertOk();
        }
    }

    public function test_static_seo_pages_use_recent_date_not_hardcoded_year(): void
    {
        $this->get('/kebijakan-privasi')
            ->assertOk()
            ->assertSee(now()->translatedFormat('d F Y'), false);
    }
}
