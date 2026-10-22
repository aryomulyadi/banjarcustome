<?php

namespace App\Support;

/**
 * Sumber data tunggal katalog asli Banjar Custome (Fase 24).
 * Dipakai oleh artisan `catalog:sync` (dev & deploy produksi).
 */
class CatalogData
{
    /**
     * Kategori final: name => [slug, description].
     */
    public static function categories(): array
    {
        return [
            'Tumbler' => [
                'slug' => 'tumbler',
                'description' => 'Tumbler & botol minum custom (stainless/aluminium) dengan cetak logo — Niagara, Sport, Sakura, dan Mug Travel.',
            ],
            'Kaos' => [
                'slug' => 'kaos',
                'description' => 'Kaos custom Cotton Combed 24s & 30s, lengan pendek dan panjang, sablon digital & manual plastisol.',
            ],
            'Polo' => [
                'slug' => 'polo',
                'description' => 'Polo shirt kerah custom bahan CVC, Lacoste PE, atau Cotton — sablon digital termasuk harga.',
            ],
            'Jersey' => [
                'slug' => 'jersey',
                'description' => 'Jersey custom (futsal, basket, voli, setelan) — harga, MOQ, dan upgrade ada di Katalog Jersey.',
            ],
            'Jaket' => [
                'slug' => 'jaket',
                'description' => 'Jaket custom hoodie zipper & coach jacket, bahan flece atau Taslan/Parasut (water resistant).',
            ],
            'Kemeja' => [
                'slug' => 'kemeja',
                'description' => 'Kemeja seragam American Drill & Nagata/Japan Drill — untuk kantor, sekolah, dan komunitas.',
            ],
            'Topi' => [
                'slug' => 'topi',
                'description' => 'Topi custom per bahan: Twill Drill/Jaring, American Drill/Ribstop, Raphel, dan Canvas/Laken — model trucker, baseball, snapback, bucket, rimba.',
            ],
            'Merchandise' => [
                'slug' => 'merchandise',
                'description' => 'Goodie bag spunbond, tote bag blacu & kanvas premium untuk event, seminar, dan promosi brand.',
            ],
        ];
    }

    /**
     * Palet38 warna standar Cotton Combed 30s: name => hex.
     */
    public static function colorPalette(): array
    {
        return [
            'Hitam' => '#1C1C1C',
            'Putih' => '#FFFFFF',
            'Abu Misty' => '#9CA3AF',
            'Abu Tua' => '#4B5563',
            'Abu Muda' => '#D1D5DB',
            'Merah' => '#DC2626',
            'Maroon' => '#7F1D1D',
            'Teracota' => '#C2410C',
            'Pink' => '#EC4899',
            'Fanta' => '#D946EF',
            'Navy' => '#1E3A8A',
            'Biru Turkis' => '#06B6D4',
            'Biru Benhur' => '#1D4ED8',
            'Biru Muda' => '#93C5FD',
            'Hijau Army' => '#4D7C0F',
            'Hijau Botol' => '#14532D',
            'Hijau Stabilo' => '#BEF264',
            'Hijau Tosca' => '#0D9488',
            'Hijau Fuji' => '#22C55E',
            'Mustard' => '#CA8A04',
            'Kuning Kenari' => '#FACC15',
            'Orange' => '#F97316',
            'Ungu' => '#7C3AED',
            'Cream' => '#F3ECD8',
            'Coklat' => '#78350F',
            'Lilac' => '#C4B5FD',
            'Hijau Sage' => '#8A9A5B',
            'Khaki' => '#B5A642',
            'Dusty Pink' => '#DCAE96',
            'Salem' => '#F87171',
            'Denim' => '#4A6FA5',
            'Almond' => '#EFE0C8',
            'Hijau Mint' => '#A7F3D0',
            'Rasberry' => '#BE123C',
            'Biru Langit' => '#7DD3FC',
            'Coklat Polisi' => '#4A3728',
            'Wood' => '#A98B6D',
            'Burgundy' => '#800020',
        ];
    }

    public static function colors24s(): array
    {
        return ['Hitam', 'Putih', 'Abu Misty', 'Merah', 'Navy', 'Maroon', 'Biru Benhur'];
    }

    public static function colors38(): array
    {
        return array_keys(self::colorPalette());
    }

    /**
     * Slug produk pengganti yang harus dihapus (katalog lama / jersey).
     */
    public static function replaceSlugs(): array
    {
        return [
            'kaos-promosi-custom',
            'sablon-kaos-satuan',
            'kaos-polos-cotton-combed-30s',
            'kaos-lengan-panjang-polos',
            'jersey-futsal-printing',
            'jersey-basket-printing',
            'jersey-voli-tim',
            'jersey-setelan-bola',
            'topi-custom-bordir',
            'totebag-kanvas-custom',
        ];
    }

    /**
     * Produk lama yang dipertahankan: slug => nama kategori tujuan.
     */
    public static function reassign(): array
    {
        return [
            'kemeja-koko-kombinasi' => 'Kemeja',
        ];
    }

    /**
     * Produk final: slug => data (updateOrCreate; category_id lewat lookup name).
     *
     * price_tiers hanya untuk produk dengan MOQ 1 lusin (min_qty 12).
     */
    public static function products(): array
    {
        $kainWarna38 = 'Mengikuti 38 variasi warna palet standar Cotton Combed 30s (lihat halaman Kaos Dewasa Cotton Combed 30s).';

        return [
            'kaos-cotton-combed-24s' => [
                'category' => 'Kaos',
                'title' => 'Kaos Cotton Combed 24s',
                'price_estimate' => 'Mulai Rp 75.000',
                'base_price' => 75000,
                'moq_lusin' => true,
                'colors' => self::colors24s(),
                'description' => implode("\n", [
                    'Lengan pendek Rp 75.000/pcs, lengan panjang Rp 85.000/pcs — harga sudah termasuk cetak sablon.',
                    'Bahan: Cotton Combed 24s (lembut, adem, jahitan rapi).',
                    'Metode cetak: Digital & Manual Plastisol.',
                    'Minimal order: 1 lusin.',
                    'Pilihan warna: Hitam, Putih, Abu Misty, Merah, Navy, Maroon, Biru Benhur.',
                ]),
            ],
            'kaos-dewasa-cotton-combed-30s' => [
                'category' => 'Kaos',
                'title' => 'Kaos Dewasa Cotton Combed 30s',
                'price_estimate' => 'Mulai Rp 65.000',
                'base_price' => 65000,
                'moq_lusin' => true,
                'colors' => self::colors38(),
                'description' => implode("\n", [
                    'Lengan pendek Rp 65.000/pcs, lengan panjang Rp 75.000/pcs — harga sudah termasuk cetak sablon.',
                    'Bahan: Cotton Combed 30s.',
                    'Metode cetak: Digital & Manual Plastisol.',
                    'Pilihan warna: '.implode(', ', self::colors38()).'.',
                ]),
            ],
            'kaos-anak-cotton-combed-30s' => [
                'category' => 'Kaos',
                'title' => 'Kaos Anak Cotton Combed 30s',
                'price_estimate' => 'Mulai Rp 45.000',
                'base_price' => 45000,
                'moq_lusin' => true,
                'colors' => self::colors38(),
                'description' => implode("\n", [
                    'Lengan pendek Rp 45.000/pcs, lengan panjang Rp 55.000/pcs — harga sudah termasuk cetak sablon.',
                    'Bahan: Cotton Combed 30s (aman & nyaman untuk anak).',
                    'Metode cetak: Digital & Manual Plastisol.',
                    $kainWarna38,
                ]),
            ],
            'polo-shirt-kerah' => [
                'category' => 'Polo',
                'title' => 'Polo Shirt Kerah',
                'price_estimate' => 'Mulai Rp 70.000',
                'base_price' => 70000,
                'moq_lusin' => true,
                'colors' => self::colors38(),
                'description' => implode("\n", [
                    'Lengan pendek Rp 70.000/pcs, lengan panjang Rp 80.000/pcs — harga sudah termasuk cetak sablon digital.',
                    'Bahan: CVC, Lacoste PE, atau Cotton (pilih sesuai kebutuhan & budget).',
                    'Metode cetak: Digital & Manual Plastisol.',
                    'Minimal order: 1 lusin.',
                    $kainWarna38,
                ]),
            ],
            'topi-twill-drill-jaring-trucker' => [
                'category' => 'Topi',
                'title' => 'Topi Twill Drill / Jaring / Trucker',
                'price_estimate' => 'Mulai Rp 15.000',
                'base_price' => 15000,
                'moq_lusin' => false,
                'colors' => ['Hitam', 'Putih', 'Navy', 'Abu Misty', 'Merah'],
                'description' => implode("\n", [
                    'Paling ekonomis untuk event, komunitas, dan promosi massal.',
                    'Range harga: Rp 15.000 - Rp 35.000/pcs (menyesuaikan model, jumlah, dan sablon).',
                    'Bahan: Twill drill, jaring (mesh), atau kombinasi trucker.',
                    'Model populer: Topi Trucker — panel jaring belakang + busa depan, cocok sablon logo.',
                ]),
            ],
            'topi-american-drill-ribstop' => [
                'category' => 'Topi',
                'title' => 'Topi American Drill / Ribstop',
                'price_estimate' => 'Mulai Rp 25.000',
                'base_price' => 25000,
                'moq_lusin' => false,
                'colors' => ['Hitam', 'Putih', 'Navy', 'Abu Misty', 'Merah'],
                'description' => implode("\n", [
                    'Bahan American drill / ribstop — serat kuat, bentuk awet, harga menengah.',
                    'Range harga: Rp 25.000 - Rp 27.000/pcs.',
                    'Cocok untuk seragam kerja, instansi, dan merchandise harian.',
                ]),
            ],
            'topi-raphel-polyester-denim-sueding' => [
                'category' => 'Topi',
                'title' => 'Topi Raphel (Polyester, Denim, Sueding)',
                'price_estimate' => 'Mulai Rp 26.000',
                'base_price' => 26000,
                'moq_lusin' => false,
                'colors' => ['Hitam', 'Putih', 'Navy', 'Abu Misty', 'Merah'],
                'description' => implode("\n", [
                    'Bahan Raphel (polyester, denim, sueding) — kesan premium, jahitan rapi.',
                    'Range harga: Rp 26.000 - Rp 35.000/pcs.',
                    'Model populer: Topi Baseball (visor melengkung) & Snapback (flat visor + pengatur snap) — ideal untuk bordir komputer.',
                ]),
            ],
            'topi-canvas-laken' => [
                'category' => 'Topi',
                'title' => 'Topi Canvas / Laken',
                'price_estimate' => 'Mulai Rp 30.000',
                'base_price' => 30000,
                'moq_lusin' => false,
                'colors' => ['Hitam', 'Putih', 'Cream'],
                'description' => implode("\n", [
                    'Bahan kanvas / laken — tebal, kuat, gaya kasual.',
                    'Range harga: Rp 30.000 - Rp 40.000/pcs.',
                    'Model populer: Bucket hat & Topi Rimba (brim lebar + tali dagu) untuk kebutuhan lapangan dan komunitas alam.',
                ]),
            ],
            'goodie-bag-spunbond' => [
                'category' => 'Merchandise',
                'title' => 'Goodie Bag Spunbond',
                'price_estimate' => 'Mulai Rp 3.000',
                'base_price' => 3000,
                'moq_lusin' => false,
                'colors' => ['Putih', 'Biru Turkis', 'Merah', 'Hijau Fuji', 'Hitam'],
                'description' => implode("\n", [
                    'Paling ekonomis untuk seminar, event, dan promosi massal.',
                    'Bahan: spunbond (non-woven) — ringan, bisa cetak logo/sablon full color.',
                    'Mulai Rp 3.000/pcs (menyesuaikan ukuran & jumlah).',
                    'Warna dasar: putih, biru, merah, hijau, hitam.',
                ]),
            ],
            'tote-bag-blacu' => [
                'category' => 'Merchandise',
                'title' => 'Tote Bag Blacu',
                'price_estimate' => 'Mulai Rp 10.000',
                'base_price' => 10000,
                'moq_lusin' => false,
                'colors' => ['Putih', 'Hitam'],
                'description' => implode("\n", [
                    'Tote bag bahan kain blacu — kain tebal, kuat, warna dasar putih tulang atau hitam.',
                    'Mulai Rp 10.000/pcs (menyesuaikan ukuran & sablon).',
                    'Cocok untuk goodie bag event, ulang toko, atau merchandise brand.',
                ]),
            ],
            'tote-bag-kanvas-premium' => [
                'category' => 'Merchandise',
                'title' => 'Tote Bag Kanvas Premium',
                'price_estimate' => 'Mulai Rp 35.000',
                'base_price' => 35000,
                'moq_lusin' => false,
                'colors' => ['Hitam', 'Putih', 'Navy'],
                'description' => implode("\n", [
                    'Tote bag kanvas tebal dengan pilihan ukuran lebih besar dan kombinasi warna tali.',
                    'Mulai Rp 35.000/pcs.',
                    'Bahan premium dan awet — cocok untuk merchandise kelas atas.',
                ]),
            ],
            'tumbler-custom' => [
                'category' => 'Tumbler',
                'title' => 'Tumbler Custom',
                'price_estimate' => 'Mulai Rp 35.000',
                'base_price' => 35000,
                'moq_lusin' => false,
                'colors' => ['Putih', 'Hitam'],
                'description' => implode("\n", [
                    'Botol minum custom stainless/aluminium dengan cetak logo atau wrap full body.',
                    'Mulai Rp 35.000/pcs (menyesuaikan varian & jumlah).',
                    'Varian: Niagara / Termos Lurus (tahan panas-dingin), Sport / Carabiner (pengait outdoor), Sakura (bawah melebar, elegan), Kopi / Mug Travel (pendek-lebar, ideal kopi/teh).',
                ]),
            ],
            'kemeja-kantor-seragam' => [
                'category' => 'Kemeja',
                'title' => 'Kemeja Kantor Seragam',
                'price_estimate' => 'Mulai Rp 95.000',
                'base_price' => 95000,
                'moq_lusin' => false,
                'colors' => ['Putih', 'Navy', 'Hitam'],
                'description' => implode("\n", [
                    'Kemeja seragam kantor/organisasi dengan jahitan rapi.',
                    'Bahan American Drill (standar — serat medium, kuat, harga menengah) atau Nagata/Japan Drill (premium — katun tinggi, jauh lebih adem & lembut).',
                    'Mulai Rp 95.000/pcs (menyesuaikan bahan & jumlah).',
                    'Cocok untuk seragam kantor, sekolah, dan komunitas.',
                ]),
            ],
            'jaket-coach-jacket' => [
                'category' => 'Jaket',
                'title' => 'Jaket Coach Jacket',
                'price_estimate' => 'Mulai Rp 135.000',
                'base_price' => 135000,
                'moq_lusin' => false,
                'colors' => ['Hitam', 'Navy'],
                'description' => implode("\n", [
                    'Coach jacket bahan Taslan/Parasut — efek daun talas (water resistant) sekaligus penahan angin (windbreaker).',
                    'Mulai Rp 135.000/pcs (menyesuaikan jumlah & sablon).',
                    'Cocok untuk jaket motor, jaket tim, dan kebutuhan outdoor.',
                ]),
            ],
            'jaket-hoodie-zipper' => [
                'category' => 'Jaket',
                'title' => 'Jaket Hoodie Zipper',
                'price_estimate' => 'Mulai Rp 150.000',
                'base_price' => 150000,
                'moq_lusin' => false,
                'colors' => ['Hitam', 'Abu Misty'],
                'description' => implode("\n", [
                    'Hoodie zipper untuk kebutuhan komunitas & merchandise.',
                    'Bahan flece/hoodie; tersedia opsi eksterior Taslan/Parasut (water resistant) untuk versi anti air.',
                    'Mulai Rp 150.000/pcs (menyesuaikan jumlah & sablon).',
                ]),
            ],
        ];
    }
}
