<?php

namespace Tests\Feature;

use App\Models\Gallery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_pages_return_successful_response(): void
    {
        $pages = [
            '/tentang',
            '/panduan-ukuran',
            '/kebijakan-privasi',
            '/syarat-ketentuan',
            '/login',
            '/galeri',
        ];

        foreach ($pages as $page) {
            $this->get($page)->assertOk();
        }
    }

    public function test_tentang_page_displays_company_info(): void
    {
        $this->get('/tentang')
            ->assertOk()
            ->assertSee('Banjar Custome')
            ->assertSee('Alur Pemesanan');
    }

    public function test_size_chart_displays_sizes(): void
    {
        $this->get('/panduan-ukuran')
            ->assertOk()
            ->assertSee('Lingkar Dada')
            ->assertSee('XXXL');
    }

    public function test_layanan_page_shows_estimation_and_payment_terms(): void
    {
        $this->get('/layanan')
            ->assertOk()
            ->assertSee('Estimasi Produksi & Pembayaran')
            ->assertSee('Kaos — 3–7 hari kerja')
            ->assertSee('Jersey — 10–12 hari')
            ->assertSee('DP 50%');
    }

    public function test_gallery_filters_by_category(): void
    {
        Gallery::create(['title' => 'Jersey Tim Putra', 'category' => 'Jersey']);
        Gallery::create(['title' => 'Kaos Kanvas Unik', 'category' => 'Kaos']);

        $this->get('/galeri?kategori=Jersey')
            ->assertOk()
            ->assertSee('Jersey Tim Putra')
            ->assertDontSee('Kaos Kanvas Unik');
    }

    public function test_gallery_paginates_twelve_items_per_page(): void
    {
        foreach (range(1, 13) as $i) {
            Gallery::create(['title' => 'Galeri Nomor '.str_pad((string) $i, 2, '0', STR_PAD_LEFT), 'category' => 'Jersey']);
        }

        $this->get('/galeri')
            ->assertOk()
            ->assertSee('Galeri Nomor 13')
            ->assertDontSee('Galeri Nomor 01')
            ->assertSee('page=2');

        $this->get('/galeri?page=2')
            ->assertOk()
            ->assertSee('Galeri Nomor 01')
            ->assertDontSee('Galeri Nomor 13');
    }

    public function test_gallery_grid_renders_lightbox_markup(): void
    {
        Gallery::create(['title' => 'Foto Lightbox', 'category' => 'Jersey', 'image' => 'galleries/foto.jpg']);

        $this->get('/galeri')
            ->assertOk()
            ->assertSee('x-data="galleryLightbox"', false)
            ->assertSee('data-lb', false)
            ->assertSee('x-ref="dialog"', false)
            ->assertSee('galleries/foto.jpg');
    }
}
