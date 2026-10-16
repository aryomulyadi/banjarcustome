<?php

namespace App\Support;

class SizeSurcharge
{
    /**
     * Tambahan harga untuk satu ukuran (rupiah).
     * Cek config('banjarcustom.size_surcharges') dulu (bisa diubah),
     * lalu fallback pola (n-2) × 10.000 untuk nXL (4XL = 30.000, dst).
     * Selain itu 0.
     */
    public static function of(string $size): int
    {
        $key = strtoupper(trim($size));
        if ($key === '') {
            return 0;
        }

        $map = config('banjarcustom.size_surcharges', []);
        if (is_array($map) && isset($map[$key]) && is_numeric($map[$key])) {
            return (int) $map[$key];
        }

        if ($key === 'XXL') {
            return 10000;
        }

        // XXXL dan 3XL sama-sama 20.000 (20.000 = (3-2) × 10.000).
        if ($key === 'XXXL') {
            return 20000;
        }

        if (preg_match('/^(\d+)XL$/', $key, $matches) && (int) $matches[1] >= 3) {
            return ((int) $matches[1] - 2) * 10000;
        }

        return 0;
    }
}
