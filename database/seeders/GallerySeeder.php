<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['title' => 'Jersey Futsal Komunitas Banjarmasin', 'caption' => 'Set jersey printing full color untuk komunitas futsal, lengkap dengan nama dan nomor.', 'category' => 'Jersey'],
            ['title' => 'Seragam Kantor Perusahaan', 'caption' => 'Produksi seragam kemeja kantor 50 pcs dengan bordir logo dada.', 'category' => 'Seragam'],
            ['title' => 'Kaos Event Promosi', 'caption' => 'Kaos promosi sablon plastisol 200 pcs untuk kegiatan event Kota Banjarmasin.', 'category' => 'Kaos'],
            ['title' => 'Jaket Komunitas Motor', 'caption' => 'Coach jacket custom komunitas dengan bordir besar di punggung.', 'category' => 'Jaket'],
            ['title' => 'Jersey Basket Sekolah', 'caption' => 'Jersey basket tim sekolah, printing dua sisi dengan cutting loose.', 'category' => 'Jersey'],
            ['title' => 'Merchandise Totebag Event', 'caption' => 'Totebag kanvas cetak full color dua sisi untuk goodie bag event.', 'category' => 'Merchandise'],
            ['title' => 'Kaos Polos Grosir Komunitas', 'caption' => 'Kaos polos cotton combed 30s grosir untuk komunitas olahraga.', 'category' => 'Kaos'],
            ['title' => 'Seragam Staf Lapangan', 'caption' => 'Kemeja seragam staf lapangan dengan bordir nama dan jabatan.', 'category' => 'Seragam'],
        ];

        foreach ($items as $item) {
            Gallery::firstOrCreate(['title' => $item['title']], $item);
        }
    }
}
