<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_hero_slides_use_x_show_so_inactive_slides_leave_tab_order(): void
    {
        $content = file_get_contents(base_path('resources/views/home.blade.php'));

        $this->assertStringContainsString('x-show="index === {{ $i }}"', $content);
        $this->assertDoesNotMatchRegularExpression(
            '/:class="index === \{\{ \$i \}\} \? .opacity-100. : .pointer-events-none opacity-0./',
            $content,
            'Slide hero harus pakai x-show (display:none), bukan hanya opacity — link slide tersembunyi masih kejangkar Tab.'
        );
    }

    public function test_header_kategori_dropdown_supports_keyboard(): void
    {
        $content = file_get_contents(base_path('resources/views/partials/header.blade.php'));

        $this->assertStringContainsString('@keydown.arrow-down.prevent', $content);
        $this->assertStringContainsString('@focusout', $content);
        $this->assertStringContainsString('x-ref="menuKategori"', $content);
    }

    public function test_mobile_menu_closes_on_escape(): void
    {
        $content = file_get_contents(base_path('resources/views/partials/header.blade.php'));

        $this->assertStringContainsString('@keydown.escape.window="mobileOpen = false"', $content);
    }

    public function test_css_defines_focus_visible_ring_and_reduced_motion(): void
    {
        $css = file_get_contents(base_path('resources/css/app.css'));

        $this->assertMatchesRegularExpression('/:focus-visible\s*\{[^}]*outline:\s*2px solid/s', $css);
        $this->assertMatchesRegularExpression('/@media \(prefers-reduced-motion: reduce\)/', $css);
    }

    public function test_hero_slider_autoplay_respects_reduced_motion(): void
    {
        $js = file_get_contents(base_path('resources/js/app.js'));

        $this->assertStringContainsString('prefers-reduced-motion: reduce', $js);
    }

    public function test_order_form_error_summary_is_focusable_and_announced(): void
    {
        $response = $this->from(route('pesan.create'))->post(route('pesan.store'), []);

        $response->assertRedirect(route('pesan.create'));

        $page = $this->get(route('pesan.create'));
        $page->assertSee('id="error-summary"', false);
        $page->assertSee('tabindex="-1"', false);
        $page->assertSee('role="alert"', false);
    }

    public function test_galeri_lightbox_dialog_has_accessible_name(): void
    {
        $content = file_get_contents(base_path('resources/views/galeri.blade.php'));

        $this->assertStringContainsString('aria-label="Pratinjau foto galeri"', $content);
    }

    public function test_public_content_pages_have_exactly_one_h1(): void
    {
        $pages = [
            '/pesan',
            '/cek-pesanan',
            '/galeri',
            '/produk',
            '/katalog-jersey',
            '/faq',
            '/tentang',
            '/layanan',
            '/login',
        ];

        foreach ($pages as $path) {
            $response = $this->get($path);
            $response->assertOk();

            $this->assertSame(
                1,
                substr_count($response->getContent(), '<h1'),
                "{$path} harus punya tepat satu <h1> (hierarki heading WCAG 1.3.1)."
            );
        }
    }

    public function test_aria_labels_only_appear_on_valid_elements_or_with_role(): void
    {
        $allowed = [
            'a' => true, 'button' => true, 'input' => true, 'select' => true,
            'textarea' => true, 'img' => true, 'summary' => true, 'details' => true,
            'output' => true, 'progress' => true, 'meter' => true, 'time' => true,
            // Landmark (role implisit) & elemen bernama implisit — aria-label sah menurut ARIA in HTML.
            'nav' => true, 'header' => true, 'footer' => true, 'main' => true,
            'aside' => true, 'form' => true, 'search' => true, 'dialog' => true,
            'section' => true, 'fieldset' => true,
        ];

        $offenders = [];

        foreach (['/', '/produk', '/galeri', '/katalog-jersey', '/pesan', '/login'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            $doc = new \DOMDocument;
            libxml_use_internal_errors(true);
            $doc->loadHTML($html);
            libxml_clear_errors();

            $xp = new \DOMXPath($doc);
            foreach ($xp->query('//*[@aria-label]') as $el) {
                $tag = strtolower($el->nodeName);
                $hasRole = $el->hasAttribute('role') && trim($el->getAttribute('role')) !== '';

                if (! isset($allowed[$tag]) && ! $hasRole) {
                    $offenders[] = sprintf(
                        '%s: <%s> tanpa role (aria-label dilarang untuk elemen non-semantik — Lighthouse aria-prohibited-attr)',
                        $path,
                        $tag
                    );
                }
            }
        }

        $this->assertSame([], $offenders, implode('; ', $offenders));
    }

    public function test_slider_dot_buttons_expand_touch_targets_to_at_least_24px(): void
    {
        $home = file_get_contents(base_path('resources/views/home.blade.php'));
        $this->assertStringContainsString(
            'class="flex h-6 w-6 items-center justify-center"',
            $home,
            'Titik slider hero harus tombol 24x24 nyata (Lighthouse target-size tidak menghitung pseudo-element).'
        );

        $catalog = file_get_contents(base_path('resources/views/catalog/show.blade.php'));
        $this->assertStringContainsString(
            'class="flex h-6 w-6 items-center justify-center"',
            $catalog,
            'Titik galeri produk harus tombol 24x24 nyata (Lighthouse target-size).'
        );
    }

    public function test_product_placeholder_uses_contrasting_foreground_token(): void
    {
        $content = file_get_contents(base_path('resources/views/components/placeholder-image.blade.php'));

        $this->assertStringContainsString(
            'text-foreground/60',
            $content,
            'Placeholder produk pakai foreground/60 (5.09 light / 6.16 dark) — muted-foreground/70 hanya 2.59/3.78 di atas bg-muted.'
        );
        $this->assertStringNotContainsString('text-muted-foreground/70', $content);
    }
}
