<?php

namespace Tests\Feature;

use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeaderNavTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, Category>
     */
    private function makeCategories(): array
    {
        return [
            Category::create(['name' => 'Jaket', 'slug' => 'jaket-nav']),
            Category::create(['name' => 'Kemeja', 'slug' => 'kemeja-nav']),
            Category::create(['name' => 'Merchandise', 'slug' => 'merch-nav']),
        ];
    }

    public function test_home_groups_categories_inside_dropdown_menu(): void
    {
        $categories = $this->makeCategories();
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('id="menu-kategori"', $html);
        $this->assertStringContainsString('aria-controls="menu-kategori"', $html);
        $this->assertStringContainsString('aria-haspopup="true"', $html);
        $this->assertStringContainsString('Kategori Produk', $html);

        preg_match('/id="menu-kategori"(.*?)<\/a>\s*<\/div>/su', $html, $panelMatch);
        $panel = $panelMatch[1] ?? '';
        $this->assertNotSame('', $panel, 'Panel dropdown kategori tidak ditemukan di HTML.');

        preg_match('/<nav[^>]*aria-label="Navigasi utama"(.*?)<\/nav>/su', $html, $navMatch);
        $desktopNav = $navMatch[1] ?? '';
        $this->assertNotSame('', $desktopNav, 'Nav desktop tidak ditemukan di HTML.');

        foreach ($categories as $category) {
            $href = route('kategori', $category->slug);

            // Satu di dropdown desktop (nav), satu di menu mobile, satu di footer —
            // tidak lagi tersebar sebagai link datar yang menyatu dengan nav.
            $this->assertSame(3, substr_count($html, $href), "Tautan {$href} tidak tepat 3 kemunculan.");
            $this->assertSame(1, substr_count($desktopNav, $href), "Tautan {$href} ganda di nav desktop.");
            $this->assertStringContainsString($href, $panel);
        }

        $this->assertStringContainsString(route('produk.index'), $panel);
    }

    public function test_jersey_entry_is_a_distinct_cta_not_a_category_link(): void
    {
        $this->makeCategories();
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertSame(3, substr_count($html, route('katalog-jersey')));

        // Pill desktop: rounded-full + warna primary, terpisah oleh divider.
        $this->assertMatchesRegularExpression(
            '/class="[^"]*rounded-full[^"]*bg-primary[^"]*"[^>]*>\s*.*?Katalog Jersey/su',
            $html
        );
        $this->assertStringContainsString('aria-hidden="true"></span>', $html);
        $this->assertStringContainsString('Harga, MOQ &amp; panduan order', $html);
    }

    public function test_mobile_menu_has_home_link_and_grouped_sections(): void
    {
        $this->makeCategories();
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('id="menu-mobile"', $html);

        $this->assertMatchesRegularExpression(
            '/<a[^>]*href="'.preg_quote(route('home'), '/').'"[^>]*>\s*Beranda\s*<\/a>/u',
            $html
        );

        // Kategori jadi label grup, bukan sekadar sederet link setara.
        $this->assertMatchesRegularExpression('/>\s*Kategori\s*<\/p>/u', $html);
        $this->assertMatchesRegularExpression('/>\s*Semua Produk\s*<\/a>/u', $html);
        $this->assertMatchesRegularExpression('/rounded-md bg-primary[^"]*"[^>]*>.*?Katalog Jersey/su', $html);
    }
}
