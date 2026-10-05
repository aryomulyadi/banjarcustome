<?php

namespace App\Support;

use App\Models\Setting;

class ServiceTypes
{
    private const SETTING_KEY = 'services.items';

    /**
     * Daftar layanan lengkap: [['title' => ..., 'desc' => ...], ...].
     */
    public static function items(): array
    {
        $stored = json_decode((string) Setting::get(self::SETTING_KEY), true);

        if (is_array($stored) && self::isValid($stored)) {
            return $stored;
        }

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

    public static function setItems(array $items): void
    {
        Setting::set(self::SETTING_KEY, json_encode(array_values($items)));
    }

    private static function isValid(array $items): bool
    {
        foreach ($items as $item) {
            if (! is_array($item) || empty($item['title'])) {
                return false;
            }
        }

        return true;
    }
}
