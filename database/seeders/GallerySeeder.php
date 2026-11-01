<?php

namespace Database\Seeders;

use App\Models\Gallery;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            [
                'title' => 'Jersey Futsal',
                'caption' => 'Set jersey printing full color untuk komunitas futsal, lengkap dengan nama dan nomor.',
                'category' => 'Jersey',
                'image' => 'galleries/OavS5V8xaJKdQSxxFyo0HzapRIugurm6ePYwHLfa.png',
            ],
            [
                'title' => 'Seragam Kantor Perusahaan',
                'caption' => 'Produksi seragam kemeja kantor 50 pcs dengan bordir logo dada.',
                'category' => 'Seragam',
                'image' => 'galleries/HuJXj6tpIewc06vYhFLlVLJI1XgDB1Gi8RaJxtpK.jpg',
            ],
            [
                'title' => 'Kaos Panjang Gathering',
                'caption' => 'Kaos lengan panjang bahan combed 30s untuk gathering komunitas Banjarmasin.',
                'category' => 'Kaos',
                'image' => 'galleries/SXJNO9YBYqUsYU6bWMoY6EoN06JB5JSXP0lB74Tc.jpg',
            ],
            [
                'title' => 'Jersey Komunitas Motor',
                'caption' => 'Jersey custom lengan panjang dengan kombinasi warna kontras untuk komunitas motor.',
                'category' => 'Jersey',
                'image' => 'galleries/DFFwfWVtiPyMHo4YhrMg8f4GEaaTspzew6fAoXL8.png',
            ],
            [
                'title' => 'Jersey Basket Sekolah',
                'caption' => 'Jersey basket tim sekolah, printing dua sisi dengan cutting loose.',
                'category' => 'Jersey',
                'image' => 'galleries/A98kr2Lj6QmreSiOG9faCxEOxof4jKqSxHOCMWzU.png',
            ],
            [
                'title' => 'Merchandise Goodiebag Event',
                'caption' => 'Totebag kanvas cetak full color dua sisi untuk goodie bag event.',
                'category' => 'Merchandise',
                'image' => 'galleries/SoUdE05EKFKIQ7MMUOLSzj7ECH7TGTZG505166sV.jpg',
            ],
            [
                'title' => 'Kaos Event',
                'caption' => 'Kaos promosi sablon plastisol 200 pcs untuk kegiatan event Kota Banjarmasin.',
                'category' => 'Kaos',
                'image' => 'galleries/GHu0mWy8EexLRVniLoJsCFcAXnzvZlnbYhG1ZOuY.jpg',
            ],
            [
                'title' => 'Tumbler',
                'caption' => 'Tumbler stainless custom logo dengan cetak laser dan UV printing untuk cinderamata.',
                'category' => 'Tumbler',
                'image' => 'galleries/ZYGfXDLPX9jqBFbsV8ZatrzlrD4dkw1TCHqIsufv.jpg',
            ],
        ];

        foreach ($items as $item) {
            Gallery::updateOrCreate(
                ['title' => $item['title']],
                $item
            );
        }
    }
}
