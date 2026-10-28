<?php

namespace Database\Seeders;

use App\Models\JerseyData;
use App\Models\MasterOption;
use Illuminate\Database\Seeder;

class OperationalOptionSeeder extends Seeder
{
    /**
     * Opsi master utk modul Data Jersey & Data Target/Produksi.
     * Idempoten: nama per kind yang sudah ada akan dilewati.
     */
    public function run(): void
    {
        $map = [
            MasterOption::KIND_DESIGNER => ['AAN', 'ARYO', 'IMUL'],
            MasterOption::KIND_JERSEY_MODEL => [
                'Pendek', 'Panjang', 'Panjang-Pendek', 'Setelan',
                'Panjang Rib', 'Pendek Rib', 'Panjang Pendek Rib',
            ],
            MasterOption::KIND_JERSEY_STATUS => JerseyData::STATUSES,
            MasterOption::KIND_TARGET_LINE => [
                'JERSEY', 'PDH', 'JERSEY+JERSEY ANAK', 'POLO', 'KAOS',
                'JERSEY+STELAN JERSEY', 'JERSEY BOXY', 'JERSEY SETELAN',
                'STELAN JERSEY', 'POLO (BORDIR)',
            ],
            MasterOption::KIND_TARGET_TYPE => [
                'PENDEK-PANJANG', 'PENDEK', 'PANJANG', 'PANJANG SANGHAI ZIPPER',
                'PENDEK V-NECK', 'PENDEK O-NECK', 'PANJANG WANGKI KANCING',
                'PANJANG WANGKI INSERT', 'PENDEK WANGKI KANCING', 'PANJANG-PENDEK',
                'PANJANG-PENDEK O-NECK', 'PANJANG V-NECK TUMPUK',
                'PANJANG V-NECK VARIASI+RIB', 'PANJANG RAGLAN',
            ],
        ];

        foreach ($map as $kind => $names) {
            foreach ($names as $index => $name) {
                MasterOption::firstOrCreate(
                    ['kind' => $kind, 'name' => $name],
                    ['hex' => null, 'fee' => 0, 'active' => true, 'sort' => $index]
                );
            }
        }
    }
}
