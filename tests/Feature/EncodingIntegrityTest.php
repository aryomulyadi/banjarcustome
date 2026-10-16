<?php

namespace Tests\Feature;

use Tests\TestCase;

class EncodingIntegrityTest extends TestCase
{
    private const VIEW_FILES = [
        'resources/views/home.blade.php',
        'resources/views/admin/profile/edit.blade.php',
        'resources/views/admin/settings/edit.blade.php',
    ];

    private const MOJIBAKE_PATTERN = '/â€|â†|Â·|Ã¢|ï»¿/';

    public function test_view_files_have_no_utf8_bom(): void
    {
        foreach (self::VIEW_FILES as $relative) {
            $path = base_path($relative);
            $this->assertFileExists($path);

            $bytes = file_get_contents($path);
            $this->assertStringStartsNotWith(
                "\xEF\xBB\xBF",
                $bytes,
                "{$relative} tidak boleh diawali UTF-8 BOM."
            );
        }
    }

    public function test_view_files_have_no_mojibake_sequences(): void
    {
        foreach (self::VIEW_FILES as $relative) {
            $path = base_path($relative);
            $content = file_get_contents($path);

            $this->assertSame(
                0,
                preg_match(self::MOJIBAKE_PATTERN, $content),
                "{$relative} mengandung teks mojibake (double-encode cp1252)."
            );
        }
    }

    public function test_home_title_uses_proper_dash_and_arrow(): void
    {
        $content = file_get_contents(base_path('resources/views/home.blade.php'));

        $this->assertStringContainsString('Banjar Custome — Jasa Konveksi', $content);
        $this->assertStringContainsString('Lihat Semua →', $content);
        $this->assertDoesNotMatchRegularExpression(self::MOJIBAKE_PATTERN, $content);
    }
}
