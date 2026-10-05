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
            ->assertSee('Banjar Custome')
            ->assertSee('Jasa Konveksi & Sablon Custom Banjarmasin')
            ->assertSee('Bebas Custom Desain')
            ->assertSee('Sablon Awet')
            ->assertSee('Melayani Ecer')
            ->assertSee('Buat Seragam/Kaos Custom Anda Sendiri!')
            ->assertSee('Topi Custom Bordir')
            ->assertDontSee('Kaos Promosi Custom')
            ->assertSee('Jl Veteran komplek halim ruko No.01')
            ->assertSee('0813-4813-8440')
            ->assertSee('3-7 Hari');
    }
}
