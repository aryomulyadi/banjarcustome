<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DeployReadinessTest extends TestCase
{
    public function test_htaccess_has_compression_and_immutable_asset_cache(): void
    {
        $htaccess = file_get_contents(base_path('public/.htaccess'));

        $this->assertStringContainsString('<IfModule mod_deflate.c>', $htaccess, 'Blok gzip harus dijaga IfModule.');
        $this->assertStringContainsString('AddOutputFilterByType DEFLATE text/html', $htaccess);
        $this->assertStringContainsString('AddOutputFilterByType DEFLATE application/javascript', $htaccess);

        $this->assertStringContainsString('<IfModule mod_headers.c>', $htaccess, 'Blok cache harus dijaga IfModule.');
        $this->assertStringContainsString('max-age=31536000, immutable', $htaccess, 'Aset Vite ber-hash harus immutable.');

        $this->assertStringContainsString('<IfModule mod_expires.c>', $htaccess, 'Blok expires harus dijaga IfModule.');
        $this->assertStringContainsString('ExpiresActive On', $htaccess);
        $this->assertStringContainsString('ExpiresByType font/woff2 "access plus 1 year"', $htaccess);

        $this->assertStringContainsString('<IfModule mod_rewrite.c>', $htaccess, 'Blok rewrite Laravel asli tetap ada.');
    }

    public function test_route_cache_and_view_cache_succeed(): void
    {
        try {
            Artisan::call('route:cache');
            Artisan::call('view:cache');

            $routeCache = glob(base_path('bootstrap/cache/routes-*.php'));

            $this->assertNotEmpty($routeCache, 'route:cache menghasilkan file cache (tidak gagal serialize closure).');
        } finally {
            Artisan::call('route:clear');
            Artisan::call('view:clear');
        }
    }

    public function test_unknown_url_renders_custom_404_via_fallback(): void
    {
        $this->get('/halaman-tidak-ada-sekali')
            ->assertNotFound()
            ->assertSee('Halaman Tidak Ditemukan')
            ->assertSee('404');
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $this->get('/up')->assertOk();
    }
}
