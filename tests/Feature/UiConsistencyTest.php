<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiConsistencyTest extends TestCase
{
    use RefreshDatabase;

    private function viewSource(string $relative): string
    {
        return file_get_contents(base_path('resources/views/'.$relative));
    }

    public function test_order_form_uses_shared_input_components_without_adhoc_markup(): void
    {
        $view = $this->viewSource('orders/create.blade.php');

        $this->assertStringNotContainsString(
            'class="flex h-10 w-full rounded-md border border-input',
            $view,
            'orders/create tidak boleh memakai input ad-hoc — gunakan x-input/x-select/x-textarea (Fase 22).'
        );

        $this->assertStringNotContainsString(
            'border-destructive aria-invalid="true" @enderror',
            $view,
            'Pola @error lama (quote memutus atribut class sehingga aria-invalid tidak pernah dirender) harus dihapus.'
        );

        $this->assertStringContainsString('<x-input', $view);
        $this->assertStringContainsString('<x-select', $view);
        $this->assertStringContainsString('<x-textarea', $view);
    }

    public function test_order_form_renders_aria_invalid_after_failed_validation(): void
    {
        $response = $this->post(route('pesan.store'), []);
        $response->assertRedirect();

        $page = $this->get(route('pesan.create'));
        $page->assertOk();
        $page->assertSee('aria-invalid="true"', false);
    }

    public function test_public_ctas_use_x_button_instead_of_adhoc_classes(): void
    {
        $files = [
            'home.blade.php',
            'errors/403.blade.php',
            'errors/404.blade.php',
            'errors/500.blade.php',
            'orders/create.blade.php',
            'orders/success.blade.php',
            'orders/track.blade.php',
            'pages/faq.blade.php',
            'catalog/index.blade.php',
            'catalog/show.blade.php',
            'pages/layanan.blade.php',
            'pages/tentang.blade.php',
            'pages/katalog-jersey.blade.php',
        ];

        foreach ($files as $file) {
            $view = $this->viewSource($file);

            $this->assertStringContainsString('<x-button', $view, "{$file} harus memakai x-button (Fase 22).");

            $this->assertDoesNotMatchRegularExpression(
                '/inline-flex h-(?:9|1[12])\b[^"]*\bbg-primary(?![-\/])/',
                $view,
                "{$file} masih punya CTA ad-hoc dengan tinggi tombol manual + bg-primary."
            );

            $this->assertDoesNotMatchRegularExpression(
                '/<a\b[^>]*\bfont-bold\b/',
                $view,
                "{$file} masih punya <a> dengan font-bold — tombol publik wajib x-button (font-semibold)."
            );
        }
    }

    public function test_x_button_exposes_canonical_sizes_and_weight(): void
    {
        $button = $this->viewSource('components/button.blade.php');

        $tokens = [
            'h-9 px-4',
            'h-10 px-4',
            'h-11 px-6',
            'h-12 px-8',
            'h-10 w-10',
            'font-semibold',
        ];

        foreach ($tokens as $token) {
            $this->assertStringContainsString(
                $token,
                $button,
                "x-button harus mempertahankan token kanonik Fase 22: {$token}"
            );
        }
    }
}
