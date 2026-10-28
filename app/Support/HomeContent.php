<?php

namespace App\Support;

class HomeContent
{
    /**
     * Fase 30: konten hero (slide banner) berjalan dari source code saja —
     * halaman Pengaturan dihapus dan penyimpanan Setting tak lagi dibaca.
     */
    public static function slides(): array
    {
        return [
            [
                'title' => 'Jasa Konveksi & Sablon Custom Banjarmasin',
                'subtitle' => 'Kaos, jersey, kemeja, jaket, hingga merchandise — produksi rapi, harga bersahabat.',
                'image' => 'images/banner-1.jpg',
            ],
            [
                'title' => 'Bebas Custom Desain',
                'subtitle' => 'Kirim desainmu sendiri atau tim kami bantu dari nol sesuai kebutuhan tim dan komunitas.',
                'image' => 'images/banner-2.jpg',
            ],
            [
                'title' => 'Melayani Ecer & Grosir',
                'subtitle' => 'Dari satuan hingga ratusan pcs. Melayani Banjarmasin dan sekitarnya, kirim ke seluruh Indonesia.',
                'image' => 'images/banner-3.jpg',
            ],
        ];
    }

    public static function stats(): array
    {
        return [
            ['value' => '500+', 'label' => 'Pesanan Selesai'],
            ['value' => '100%', 'label' => 'Sablon Anti Pecah'],
            ['value' => '3-7 Hari', 'label' => 'Estimasi Produksi'],
        ];
    }
}
