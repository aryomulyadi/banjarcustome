<?php

namespace Tests\Feature;

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
}
