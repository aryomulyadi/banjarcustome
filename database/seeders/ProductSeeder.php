<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['category' => 'Sablon', 'title' => 'Kaos Promosi Custom', 'price_estimate' => 'Mulai Rp 45.000', 'description' => 'Kaos promosi dengan sablon depan-belakang, cocok untuk event, kampanye, dan branding perusahaan. Bisa satuan maupun grosir.', 'colors' => ['Hitam', 'Putih', 'Merah', 'Navy']],
            ['category' => 'Sablon', 'title' => 'Sablon Kaos Satuan', 'price_estimate' => 'Mulai Rp 55.000', 'description' => 'Layanan sablon satuan untuk komunitas dan keperluan pribadi. Desain bebas, hasil sablon rapi dan tahan lama.', 'colors' => ['Putih', 'Hitam', 'Abu-abu']],
            ['category' => 'Kaos Polos', 'title' => 'Kaos Polos Cotton Combed 30s', 'price_estimate' => 'Mulai Rp 38.000', 'description' => 'Kaos polos cotton combed 30s yang adem dan nyaman. Tersedia berbagai warna, siap sablon atau dipakai langsung.', 'colors' => ['Hitam', 'Putih', 'Navy', 'Abu-abu', 'Kuning']],
            ['category' => 'Kaos Polos', 'title' => 'Kaos Lengan Panjang Polos', 'price_estimate' => 'Mulai Rp 58.000', 'description' => 'Kaos lengan panjang bahan cotton, cocok untuk seragam komunitas atau kegiatan outdoor.', 'colors' => ['Hitam', 'Putih', 'Abu-abu', 'Navy']],
            ['category' => 'Jersey Printing', 'title' => 'Jersey Futsal Printing', 'price_estimate' => 'Mulai Rp 85.000', 'description' => 'Jersey futsal printing full color bebas custom nama dan nomor punggung. Bahan dry-fit yang menyerap keringat.', 'colors' => ['Biru', 'Merah', 'Hijau', 'Hitam']],
            ['category' => 'Jersey Printing', 'title' => 'Jersey Basket Printing', 'price_estimate' => 'Mulai Rp 95.000', 'description' => 'Jersey basket cutting loose dengan sablon printing anti luntur, desain tim bebas dari nol.', 'colors' => ['Oranye', 'Hitam', 'Navy']],
            ['category' => 'Jersey Printing', 'title' => 'Jersey Voli Tim', 'price_estimate' => 'Mulai Rp 88.000', 'description' => 'Jersey voli custom untuk klub dan komunitas, pilihan ukuran lengkap dari S sampai XXXL.', 'colors' => ['Toska', 'Kuning', 'Maroon']],
            ['category' => 'Kemeja', 'title' => 'Kemeja Kantor Seragam', 'price_estimate' => 'Mulai Rp 95.000', 'description' => 'Kemeja seragam kantor bahan drill nyaman, bisa tambah bordir logo perusahaan dan nama karyawan.', 'colors' => ['Putih', 'Navy', 'Abu-abu']],
            ['category' => 'Kemeja', 'title' => 'Kemeja Koko Kombinasi', 'price_estimate' => 'Mulai Rp 85.000', 'description' => 'Kemeja koko kombinasi modern untuk kegiatan keagamaan dan acara formal, bahan premium.', 'colors' => ['Putih', 'Hitam', 'Navy']],
            ['category' => 'Jaket', 'title' => 'Jaket Hoodie Zipper', 'price_estimate' => 'Mulai Rp 150.000', 'description' => 'Jaket hoodie zipper dengan bahan fleece tebal, cocok untuk komunitas dan brand apparel.', 'colors' => ['Hitam', 'Abu-abu', 'Navy']],
            ['category' => 'Jaket', 'title' => 'Jaket Coach Jacket', 'price_estimate' => 'Mulai Rp 135.000', 'description' => 'Coach jacket anti air dengan sablon atau bordir custom, pilihan jaket favorit komunitas anak muda.', 'colors' => ['Hitam', 'Hijau', 'Maroon']],
            ['category' => 'Merchandise', 'title' => 'Totebag Kanvas Custom', 'price_estimate' => 'Mulai Rp 35.000', 'description' => 'Totebag kanvas custom untuk goodie bag, event, dan promosi brand. Cetak full color dua sisi.', 'colors' => ['Putih', 'Hitam', 'Navy']],
            ['category' => 'Merchandise', 'title' => 'Topi Custom Bordir', 'price_estimate' => 'Mulai Rp 42.000', 'description' => 'Topi custom bordir logo untuk seragam, komunitas, dan kegiatan lapangan.', 'colors' => ['Hitam', 'Navy', 'Merah']],
        ];

        $palette = [
            'Hitam' => '#1C1C1C',
            'Putih' => '#FFFFFF',
            'Navy' => '#1E3A8A',
            'Merah' => '#DC2626',
            'Abu-abu' => '#6B7280',
            'Kuning' => '#EAB308',
            'Hijau' => '#16A34A',
            'Biru' => '#2563EB',
            'Maroon' => '#7F1D1D',
            'Oranye' => '#EA580C',
            'Toska' => '#0D9488',
        ];

        foreach ($products as $data) {
            $category = Category::where('name', $data['category'])->firstOrFail();

            $product = Product::firstOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'category_id' => $category->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'price_estimate' => $data['price_estimate'],
                ]
            );

            foreach ($data['colors'] as $colorName) {
                $product->colors()->firstOrCreate(
                    ['name' => $colorName],
                    ['hex' => $palette[$colorName]]
                );
            }
        }
    }
}
