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
            ->assertSee('<script type="application/ld+json"', false)
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

    public function test_custom_error_pages_403_and_500_render(): void
    {
        $this->assertStringContainsString('403', view('errors.403')->render());
        $this->assertStringContainsString('Akses Ditolak', view('errors.403')->render());
        $this->assertStringContainsString('500', view('errors.500')->render());
        $this->assertStringContainsString('Terjadi Gangguan', view('errors.500')->render());
    }

    public function test_security_headers_are_set_on_responses(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy');

        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertNotSame('', $csp);
        $this->assertStringContainsString("default-src 'self'", $csp);
        $this->assertStringContainsString("script-src 'self' 'unsafe-eval' 'nonce-", $csp);
        $this->assertStringContainsString("frame-ancestors 'self'", $csp);
        $this->assertStringContainsString("form-action 'self'", $csp);
        $this->assertStringContainsString("object-src 'none'", $csp);
        $this->assertStringContainsString('frame-src', $csp);
    }

    public function test_inline_scripts_carry_csp_nonce(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<script nonce="', false)
            ->assertSee('type="application/ld+json" nonce="', false);
    }

    public function test_home_has_website_and_organization_json_ld(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('"@type":"WebSite"', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"potentialAction"', false);
    }

    public function test_product_page_has_breadcrumb_json_ld(): void
    {
        $category = Category::create(['name' => 'Kaos', 'slug' => 'kaos']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Kaos Breadcrumb Tes',
            'slug' => 'kaos-breadcrumb-tes',
        ]);

        $this->get(route('produk.show', $product->slug))
            ->assertOk()
            ->assertSee('"@type":"BreadcrumbList"', false)
            ->assertSee('"name":"Beranda"', false)
            ->assertSee('"name":"Kaos"', false)
            ->assertSee('"name":"Kaos Breadcrumb Tes"', false);
    }

    public function test_catalog_and_galleries_have_breadcrumb_json_ld(): void
    {
        $this->get('/produk')
            ->assertOk()
            ->assertSee('"@type":"BreadcrumbList"', false);

        $this->get('/galeri')
            ->assertOk()
            ->assertSee('"@type":"BreadcrumbList"', false);
    }

    public function test_sitemap_includes_lastmod_and_image_for_products(): void
    {
        $category = Category::create(['name' => 'Jersey', 'slug' => 'jersey']);
        $product = Product::create([
            'category_id' => $category->id,
            'title' => 'Jersey Berfoto',
            'slug' => 'jersey-berfoto-tes',
            'image' => 'products/jersey-berfoto.jpg',
        ]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"', false)
            ->assertSee('<lastmod>'.$product->updated_at->toDateString().'</lastmod>', false)
            ->assertSee('<image:loc>'.asset('storage/products/jersey-berfoto.jpg').'</image:loc>', false);
    }

    public function test_open_graph_has_image_dimensions_and_apple_touch_icon(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<meta property="og:image:width" content="1600"', false)
            ->assertSee('<meta property="og:image:height" content="700"', false)
            ->assertSee('<meta property="og:image:alt"', false)
            ->assertSee('rel="apple-touch-icon"', false);
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
