<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class EncodingIntegrityTest extends TestCase
{
    private const MOJIBAKE_PATTERN = '/â€|â†|Â·|Ã¢|ï»¿/';

    /**
     * Semua file view (rekursif) + app.css. Cakupan penuh agar mojibake
     * seperti 'â€”' (double-encode cp1252 utk em dash) tak pernah lolos lagi.
     *
     * @return array<int, string>
     */
    private function scanTargets(): array
    {
        $files = collect(File::allFiles(resource_path('views')))
            ->map(fn ($file) => $file->getPathname())
            ->all();

        $files[] = resource_path('css/app.css');

        return $files;
    }

    private function relativePath(string $path): string
    {
        return ltrim(str_replace(base_path(), '', $path), DIRECTORY_SEPARATOR);
    }

    public function test_view_files_have_no_utf8_bom(): void
    {
        $failures = [];

        foreach ($this->scanTargets() as $path) {
            $this->assertFileExists($path);
            $bytes = file_get_contents($path);

            if (str_starts_with($bytes, "\xEF\xBB\xBF")) {
                $failures[] = $this->relativePath($path);
            }
        }

        $this->assertSame([], $failures, 'File berikut diawali UTF-8 BOM: '.implode(', ', $failures));
    }

    public function test_view_files_have_no_mojibake_sequences(): void
    {
        $failures = [];

        foreach ($this->scanTargets() as $path) {
            $content = file_get_contents($path);

            if (preg_match(self::MOJIBAKE_PATTERN, $content)) {
                $failures[] = $this->relativePath($path);
            }
        }

        $this->assertSame([], $failures, 'File berikut mengandung teks mojibake (double-encode cp1252): '.implode(', ', $failures));
    }

    public function test_home_title_uses_proper_dash_and_arrow(): void
    {
        $content = file_get_contents(base_path('resources/views/home.blade.php'));

        $this->assertStringContainsString('Banjar Custome — Jasa Konveksi', $content);
        $this->assertStringContainsString('Lihat Semua →', $content);
        $this->assertDoesNotMatchRegularExpression(self::MOJIBAKE_PATTERN, $content);
    }
}
