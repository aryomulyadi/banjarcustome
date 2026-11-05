<?php

namespace Tests\Feature;

use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_displays_core_content(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Banjar Custom')
            ->assertSee('Jasa Konveksi & Sablon Custom Banjarmasin')
            ->assertSee('Alur Pemesanan')
            ->assertSee('Konsultasi & Pilih Produk')
            ->assertSee('Sudah Dipercaya Berbagai Instansi')
            ->assertSee('Buat Seragam/Kaos Custom Anda Sendiri!')
            ->assertSee('Tumbler Custom')
            ->assertDontSee('Kaos Promosi Custom')
            ->assertSee('Jl Veteran komplek halim ruko No.01')
            ->assertSee('0813-4813-8440')
            ->assertSee('5000+')
            ->assertSee('Cabang Kami')
            ->assertSee('Banjarbaru')
            ->assertSee('Sekolah Dasar')
            ->assertDontSee('3-7 Hari')
            ->assertDontSee('Kata Mereka');
    }
}
