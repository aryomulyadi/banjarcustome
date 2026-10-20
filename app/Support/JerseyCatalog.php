<?php

namespace App\Support;

use App\Models\MasterOption;

/**
 * Akses opsi jersey (kerah & bahan) dari data master admin.
 * Bila suatu jenis belum ada isinya di database, fallback ke config.
 */
class JerseyCatalog
{
    /** @return list<array{name: string, fee: int, image: string|null}> */
    public static function collars(): array
    {
        return self::options(MasterOption::KIND_COLLAR, 'collars');
    }

    /** @return list<array{name: string, fee: int, image: string|null}> */
    public static function fabrics(): array
    {
        return self::options(MasterOption::KIND_FABRIC, 'fabrics');
    }

    /**
     * Konfigurasi jersey utk kalkulator: aturan dasar dari config,
     * daftar opsi upgrade dari database (konsisten dgn katalog & form).
     *
     * @return array<string, mixed>
     */
    public static function config(): array
    {
        $config = config('banjarcustom.jersey', []);

        $config['collars'] = self::collars();
        $config['fabrics'] = self::fabrics();

        return $config;
    }

    /**
     * @param  string  $configKey  'collars' | 'fabrics'
     * @return list<array{name: string, fee: int, image: string|null}>
     */
    private static function options(string $kind, string $configKey): array
    {
        $rows = MasterOption::query()
            ->where('kind', $kind)
            ->active()
            ->ordered()
            ->get();

        if ($rows->isEmpty()) {
            return collect(config("banjarcustom.jersey.{$configKey}", []))
                ->map(fn (array $option): array => [
                    'name' => (string) ($option['name'] ?? ''),
                    'fee' => (int) ($option['fee'] ?? 0),
                    'image' => null,
                ])
                ->values()
                ->all();
        }

        return $rows
            ->map(fn (MasterOption $option): array => [
                'name' => $option->name,
                'fee' => (int) $option->fee,
                'image' => $option->image,
            ])
            ->values()
            ->all();
    }
}
