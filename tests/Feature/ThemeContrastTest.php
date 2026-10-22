<?php

namespace Tests\Feature;

use Tests\TestCase;

class ThemeContrastTest extends TestCase
{
    /**
     * Pasangan token yang wajib memenuhi WCAG 1.4.3 (teks) / 1.4.11 (non-teks).
     * `border`/`input` DIBIARKAN rendah (1.26-1.30) — pengecualian tercatat di
     * prd.txt (Fase 19): input diberi border tebal 1px + fokus ring tebal.
     * Elemen ke-5 opsional = alpha foreground (komposit di atas bg token ke-3).
     *
     * @var array<string, array{string, string, string, float, float}>
     */
    private const PAIRS = [
        'light: foreground/background' => ['light', 'foreground', 'background', 4.5],
        'light: card-foreground/card' => ['light', 'card-foreground', 'card', 4.5],
        'light: popover-foreground/popover' => ['light', 'popover-foreground', 'popover', 4.5],
        'light: primary-foreground/primary' => ['light', 'primary-foreground', 'primary', 4.5],
        'light: primary/background (link)' => ['light', 'primary', 'background', 4.5],
        'light: destructive-foreground/destructive' => ['light', 'destructive-foreground', 'destructive', 4.5],
        'light: destructive/background (teks)' => ['light', 'destructive', 'background', 4.5],
        'light: muted-foreground/background' => ['light', 'muted-foreground', 'background', 4.5],
        'light: accent-foreground/accent' => ['light', 'accent-foreground', 'accent', 4.5],
        'light: secondary-foreground/secondary' => ['light', 'secondary-foreground', 'secondary', 4.5],
        'light: ring/background (non-teks)' => ['light', 'ring', 'background', 3.0],
        'light: foreground/60 di atas muted (placeholder gambar)' => ['light', 'foreground', 'muted', 4.5, 0.6],
        'dark: foreground/background' => ['dark', 'foreground', 'background', 4.5],
        'dark: card-foreground/card' => ['dark', 'card-foreground', 'card', 4.5],
        'dark: popover-foreground/popover' => ['dark', 'popover-foreground', 'popover', 4.5],
        'dark: primary-foreground/primary' => ['dark', 'primary-foreground', 'primary', 4.5],
        'dark: primary/background (link)' => ['dark', 'primary', 'background', 4.5],
        'dark: destructive-foreground/destructive' => ['dark', 'destructive-foreground', 'destructive', 4.5],
        'dark: destructive/background (teks)' => ['dark', 'destructive', 'background', 4.5],
        'dark: muted-foreground/background' => ['dark', 'muted-foreground', 'background', 4.5],
        'dark: accent-foreground/accent' => ['dark', 'accent-foreground', 'accent', 4.5],
        'dark: secondary-foreground/secondary' => ['dark', 'secondary-foreground', 'secondary', 4.5],
        'dark: ring/background (non-teks)' => ['dark', 'ring', 'background', 3.0],
        'dark: foreground/60 di atas muted (placeholder gambar)' => ['dark', 'foreground', 'muted', 4.5, 0.6],
    ];

    public function test_theme_token_pairs_meet_wcag_contrast(): void
    {
        $css = $this->css();
        $tokens = [
            'light' => $this->tokens($this->block($css, ':root')),
            'dark' => $this->tokens($this->block($css, '.dark')),
        ];

        foreach (self::PAIRS as $label => $pair) {
            [$theme, $fg, $bg, $min] = $pair;
            $alpha = $pair[4] ?? 1.0;

            $this->assertArrayHasKey($fg, $tokens[$theme], "{$label}: token --{$fg} hilang");
            $this->assertArrayHasKey($bg, $tokens[$theme], "{$label}: token --{$bg} hilang");

            $ratio = $this->contrast($tokens[$theme][$fg], $tokens[$theme][$bg], $alpha);
            $this->assertGreaterThanOrEqual(
                $min,
                $ratio,
                sprintf('%s = %.2f (min %.1f)', $label, $ratio, $min)
            );
        }
    }

    public function test_root_declares_color_scheme_light_dark(): void
    {
        $this->assertMatchesRegularExpression(
            '/:root\s*\{[^}]*color-scheme:\s*light dark/s',
            $this->css()
        );
    }

    public function test_dark_theme_declares_color_scheme_dark(): void
    {
        $this->assertMatchesRegularExpression(
            '/\.dark\s*\{[^}]*color-scheme:\s*dark/s',
            $this->css()
        );
    }

    public function test_native_select_options_use_theme_tokens(): void
    {
        $this->assertMatchesRegularExpression(
            '/select\s*,\s*option\s*\{[^}]*var\(--foreground\)[^}]*var\(--background\)/s',
            $this->css()
        );
    }

    public function test_error_page_applies_saved_theme(): void
    {
        $response = $this->get('/halaman-tidak-ada-xyz');

        $response->assertStatus(404);
        $response->assertSee("localStorage.getItem('theme')", false);
    }

    private function css(): string
    {
        return file_get_contents(base_path('resources/css/app.css'));
    }

    private function block(string $css, string $selector): string
    {
        $this->assertSame(
            1,
            preg_match('/'.preg_quote($selector, '/').'\s*\{(.*?)\}/s', $css, $m),
            "Selector {$selector} tidak ditemukan di app.css"
        );

        return $m[1];
    }

    /**
     * @return array<string, array{float, float, float}>
     */
    private function tokens(string $block): array
    {
        preg_match_all(
            '/--([a-z-]+):\s*(\d+(?:\.\d+)?)\s+(\d+(?:\.\d+)?)%\s+(\d+(?:\.\d+)?)%/',
            $block,
            $matches,
            PREG_SET_ORDER
        );

        $tokens = [];
        foreach ($matches as $match) {
            $tokens[$match[1]] = [(float) $match[2], (float) $match[3], (float) $match[4]];
        }

        return $tokens;
    }

    /**
     * @param  array{float, float, float}  $a
     * @param  array{float, float, float}  $b
     */
    private function contrast(array $a, array $b, float $alpha = 1.0): float
    {
        $lb = $this->luminanceRgb($this->hslToRgb($b[0], $b[1], $b[2]));

        if ($alpha < 1.0) {
            $fg = $this->hslToRgb($a[0], $a[1], $a[2]);
            $bg = $this->hslToRgb($b[0], $b[1], $b[2]);
            $la = $this->luminanceRgb([
                $fg[0] * $alpha + $bg[0] * (1 - $alpha),
                $fg[1] * $alpha + $bg[1] * (1 - $alpha),
                $fg[2] * $alpha + $bg[2] * (1 - $alpha),
            ]);
        } else {
            $la = $this->luminanceRgb($this->hslToRgb($a[0], $a[1], $a[2]));
        }

        $light = max($la, $lb);
        $dark = min($la, $lb);

        return round(($light + 0.05) / ($dark + 0.05), 2);
    }

    /**
     * @param  array{float, float, float}  $rgb
     */
    private function luminanceRgb(array $rgb): float
    {
        $channels = [];
        foreach ($rgb as $value) {
            $value /= 255;
            $channels[] = $value <= 0.04045
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        }

        return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
    }

    /**
     * @return array{float, float, float}
     */
    private function hslToRgb(float $h, float $s, float $l): array
    {
        $s /= 100;
        $l /= 100;
        $chroma = (1 - abs(2 * $l - 1)) * $s;
        $x = $chroma * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $chroma / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$chroma, $x, 0.0],
            $h < 120 => [$x, $chroma, 0.0],
            $h < 180 => [0.0, $chroma, $x],
            $h < 240 => [0.0, $x, $chroma],
            $h < 300 => [$x, 0.0, $chroma],
            default => [$chroma, 0.0, $x],
        };

        return [($r + $m) * 255, ($g + $m) * 255, ($b + $m) * 255];
    }
}
