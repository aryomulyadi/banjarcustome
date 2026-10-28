<?php

namespace App\Support;

class ServiceTypes
{
    /**
     * Daftar layanan lengkap: [['title' => ..., 'desc' => ...], ...].
     *
     * Fase 30: hanya dari config (source code) — fitur penyuntingan pada
     * halaman Pengaturan dihapus dan penyimpanan Setting tak lagi dibaca.
     */
    public static function items(): array
    {
        return config('banjarcustom.services', []);
    }

    /**
     * Hanya judul — untuk <select> form pesanan (batas kolom 60 karakter).
     */
    public static function titles(): array
    {
        return array_values(array_filter(array_map(
            fn (array $item) => $item['title'] ?? null,
            self::items()
        )));
    }

    /**
     * Deteksi judul layanan jersey (mis. "Printing Sublim — Jersey").
     */
    public static function isJerseyTitle(?string $title): bool
    {
        return $title !== null && str_contains(strtolower($title), 'jersey');
    }
}
