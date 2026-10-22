<?php

namespace Tests\Feature;

use Symfony\Component\Finder\Finder;
use Tests\TestCase;

class BladeSyntaxGuardTest extends TestCase
{
    public function test_no_blade_file_contains_unclosed_echo_before_php_close(): void
    {
        $files = (new Finder)
            ->files()
            ->name('*.blade.php')
            ->in(base_path('resources/views'));

        foreach ($files as $file) {
            $content = file_get_contents($file->getPathname());

            $this->assertSame(
                0,
                preg_match('/\{\{[^{}]*\?>/', $content),
                $file->getRelativePathname().' mengandung literal `{{ ... ?>` (kurung kurawal tak tertutup — lihat insiden Fase 19/commit 999b5e2).'
            );
        }
    }

    public function test_order_show_warna_field_uses_closed_echo(): void
    {
        $content = file_get_contents(base_path('resources/views/admin/orders/show.blade.php'));

        $this->assertStringContainsString("old('warna', \$order->warna) }}\"", $content);
        $this->assertDoesNotMatchRegularExpression("/old\('warna'[^)]*\)\s*\?>/", $content);
    }
}
