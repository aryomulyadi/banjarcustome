<?php

namespace Database\Seeders;

use App\Models\MasterOption;
use Illuminate\Database\Seeder;

class JerseyOptionSeeder extends Seeder
{
    /**
     * Isi opsi kerah & bahan jersey dari config bila belum ada di database.
     * Idempoten: jenis yang sudah terisi akan dilewati.
     */
    public function run(): void
    {
        $map = [
            MasterOption::KIND_COLLAR => 'collars',
            MasterOption::KIND_FABRIC => 'fabrics',
        ];

        foreach ($map as $kind => $configKey) {
            if (MasterOption::query()->where('kind', $kind)->exists()) {
                continue;
            }

            foreach (config("banjarcustom.jersey.{$configKey}", []) as $index => $option) {
                MasterOption::create([
                    'kind' => $kind,
                    'name' => (string) ($option['name'] ?? ''),
                    'fee' => (int) ($option['fee'] ?? 0),
                    'active' => true,
                    'sort' => $index,
                ]);
            }
        }
    }
}
