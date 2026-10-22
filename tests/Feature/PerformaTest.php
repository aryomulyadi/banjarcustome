<?php

namespace Tests\Feature;

use App\Support\ImageOptimizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PerformaTest extends TestCase
{
    use RefreshDatabase;

    public function test_static_fonts_are_served_outside_vite_without_build_warning(): void
    {
        $fontsCss = base_path('public/fonts/fonts.css');

        $this->assertFileExists($fontsCss, 'fonts.css wajib ada di public/fonts (dipindah dari app.css — Fase 21).');
        $this->assertSame(
            8,
            substr_count(file_get_contents($fontsCss), '@font-face'),
            'fonts.css harus punya 8 @font-face (400/500/600/700 x latin/latin-ext).'
        );

        $this->assertStringNotContainsString(
            '@font-face',
            file_get_contents(base_path('resources/css/app.css')),
            '@font-face tidak boleh kembali ke app.css — memicu warning Vite "didn\'t resolve" saat build.'
        );

        foreach (['400', '500', '600', '700'] as $weight) {
            foreach (['latin', 'latin-ext'] as $variant) {
                $this->assertFileExists(
                    base_path("public/fonts/instrument-sans-{$weight}-{$variant}.woff2")
                );
            }
        }
    }

    public function test_layouts_link_font_stylesheet_and_preload_above_fold_weight(): void
    {
        $app = file_get_contents(base_path('resources/views/components/layouts/app.blade.php'));

        $this->assertStringContainsString("asset('fonts/fonts.css')", $app);
        $this->assertStringContainsString('instrument-sans-700-latin.woff2', $app, 'Preload font 700 (heading above-the-fold) wajib ada.');

        $admin = file_get_contents(base_path('resources/views/components/layouts/admin.blade.php'));
        $this->assertStringContainsString("asset('fonts/fonts.css')", $admin);

        foreach (['403', '404', '500'] as $error) {
            $view = file_get_contents(base_path("resources/views/errors/{$error}.blade.php"));
            $this->assertStringContainsString("asset('fonts/fonts.css')", $view, "Halaman error {$error} harus memuat fonts.css.");
        }
    }

    public function test_home_page_links_fonts_css(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.asset('fonts/fonts.css').'"', false);
    }

    public function test_static_banner_images_have_smaller_webp_variants_with_same_dimensions(): void
    {
        $jpgs = glob(public_path('images/banner-*.jpg'));
        $this->assertNotEmpty($jpgs, 'Setidaknya satu banner jpg statis diharapkan di public/images.');

        foreach ($jpgs as $jpg) {
            $webp = preg_replace('/\.jpe?g$/i', '.webp', $jpg);
            $this->assertFileExists($webp, basename($jpg).' butuh varian WebP (Lighthouse modern-image-formats).');

            $jpgSize = getimagesize($jpg);
            $webpSize = getimagesize($webp);

            $this->assertSame((int) $jpgSize[0], (int) $webpSize[0], basename($webp).' lebar beda dari aslinya.');
            $this->assertSame((int) $jpgSize[1], (int) $webpSize[1], basename($webp).' tinggi beda dari aslinya.');
            $this->assertLessThan(
                filesize($jpg),
                filesize($webp),
                basename($webp).' harus lebih kecil dari '.basename($jpg).' (tujuan konversi).'
            );
        }
    }

    public function test_home_hero_serves_webp_variant_when_available(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/<img[^>]+src="[^"]*images\/banner-1\.webp"/',
            $html,
            'Hero slide pertama harus menyajikan banner-1.webp bila varian ada (LCP lebih ringan).'
        );
    }

    public function test_home_hero_declares_responsive_srcset_and_sizes(): void
    {
        $html = $this->get('/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression(
            '/srcset="[^"]*banner-1-1024\.webp 1024w[^"]*banner-1\.webp 1600w"/',
            $html,
            'Hero harus punya srcset 1024w/1600w (Lighthouse uses-responsive-images).'
        );
        $this->assertStringContainsString('sizes="(min-width: 1280px) 1216px', $html);
    }

    public function test_static_banners_have_1024w_variants(): void
    {
        $banners = glob(public_path('images/banner-*.jpg'));
        $this->assertNotEmpty($banners, 'Setidaknya satu banner jpg diharapkan.');

        foreach ($banners as $jpg) {
            $variant = preg_replace('/\.jpe?g$/i', '-1024.webp', $jpg);
            $this->assertFileExists($variant, basename($jpg).' butuh varian -1024.webp untuk srcset hero.');
        }
    }

    public function test_card_images_declare_sizes_attribute_for_srcset_selection(): void
    {
        $cards = [
            'components/product-card.blade.php',
            'galeri.blade.php',
            'catalog/show.blade.php',
        ];

        foreach ($cards as $view) {
            $content = file_get_contents(base_path('resources/views/'.$view));
            $this->assertStringContainsString(
                'sizes="',
                $content,
                "{$view}: gambar kartu wajib punya atribut sizes agar srcset terpilih benar."
            );
        }
    }

    public function test_image_optimizer_creates_responsive_webp_variants(): void
    {
        Storage::fake('public');

        $path = ImageOptimizer::store(UploadedFile::fake()->image('foto-produk.png', 1600, 1200), 'products');

        Storage::disk('public')->assertExists(ImageOptimizer::webpPath($path));
        Storage::disk('public')->assertExists(ImageOptimizer::responsivePath($path, 400));
        Storage::disk('public')->assertExists(ImageOptimizer::responsivePath($path, 800));
        $this->assertSame([400, 800], array_keys(ImageOptimizer::responsiveVariants($path)));
    }

    public function test_image_optimizer_skips_wide_variants_when_source_is_smaller(): void
    {
        Storage::fake('public');

        $path = ImageOptimizer::store(UploadedFile::fake()->image('kecil.png', 600, 450), 'products');

        $this->assertSame([400], array_keys(ImageOptimizer::responsiveVariants($path)));
        Storage::disk('public')->assertMissing(ImageOptimizer::responsivePath($path, 800));
    }

    public function test_image_optimizer_delete_removes_responsive_variants(): void
    {
        Storage::fake('public');

        $path = ImageOptimizer::store(UploadedFile::fake()->image('besar.png', 1600, 1200), 'products');
        ImageOptimizer::delete($path);

        Storage::disk('public')->assertMissing($path);
        Storage::disk('public')->assertMissing(ImageOptimizer::webpPath($path));
        Storage::disk('public')->assertMissing(ImageOptimizer::responsivePath($path, 400));
        Storage::disk('public')->assertMissing(ImageOptimizer::responsivePath($path, 800));
    }
}
