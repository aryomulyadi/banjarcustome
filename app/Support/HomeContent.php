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
            ['value' => '5000+', 'label' => 'Pesanan Selesai'],
        ];
    }

    /**
     * Section "Dipercaya oleh" (marquee logo klien) di beranda.
     * Placeholder memakai segmen pelanggan — saat logo asli tersedia, isi
     * 'image' dengan path file di public/ (mis. images/clients/pln.png);
     * view tidak perlu diubah.
     *
     * @return array<int, array{name: string, image: string|null}>
     */
    public static function clients(): array
    {
        return [
            ['name' => 'Instansi Pemerintah', 'image' => 'images/clients/Logo1.webp'],
            ['name' => 'Sekolah & Kampus', 'image' => 'images/clients/Logo2.png'],
            ['name' => 'Perusahaan Lokal', 'image' => 'images/clients/Logo3.webp'],
            ['name' => 'Komunitas & Ormas', 'image' => 'images/clients/Logo4.png'],
            ['name' => 'Tim Olahraga', 'image' => 'images/clients/Logo5.webp'],
            ['name' => 'Event Organizer', 'image' => 'images/clients/Logo6.png'],
            ['name' => 'UMKM & Brand', 'image' => 'images/clients/Logo7.webp'],
            ['name' => 'Organisasi Kampus', 'image' => 'images/clients/Logo8.webp'],
            ['name' => 'Sekolah Dasar', 'image' => 'images/clients/Logo9.png'],

        ];
    }
}
