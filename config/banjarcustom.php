<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Banjar Custome — Informasi Kontak
    |--------------------------------------------------------------------------
    */

    'address' => 'Jl Veteran komplek halim ruko No.01 pagar hijau seberang SMP 7, Kuripan, Kec. Banjarmasin Tim., Kota Banjarmasin, Kalimantan Selatan 70239',

    'whatsapp' => env('BC_WHATSAPP', '0813-4813-8440'),

    'whatsapp_number' => env('BC_WHATSAPP_NUMBER', '6281348138440'),

    'whatsapp_link' => 'https://wa.me/'.env('BC_WHATSAPP_NUMBER', '6281348138440'),

    'instagram' => 'https://instagram.com/banjarcustom_konveksi.id',

    'instagram_handle' => '@banjarcustom_konveksi.id',

    // Email notifikasi pesanan baru (boleh dikosongkan; bisa diubah kapan saja).
    'admin_email' => env('BC_ADMIN_EMAIL'),

    // Biaya tambahan pesanan express (opsional). Kosongkan = tampil teks generik "biaya tambahan".
    'express_fee' => env('BC_EXPRESS_FEE'),

    'open_hours' => [
        'Minggu' => 'Tutup',
        'Senin' => '09.00 – 17.00 WITA',
        'Selasa' => '09.00 – 17.00 WITA',
        'Rabu' => '09.00 – 17.00 WITA',
        'Kamis' => '09.00 – 17.00 WITA',
        'Jumat' => '09.00 – 17.00 WITA',
        'Sabtu' => '09.00 – 17.00 WITA',
    ],

    // Format schema.org untuk structured data (LocalBusiness).
    'opening_hours_schema' => [
        'Mo-Sa 09:00-17:00',
    ],

    // Listing Google Maps (nama di Google: "CUSTOM JERSEY DAN KAOS") — tombol "Buka di Google Maps".
    'maps_place_url' => 'https://www.google.com/maps/place/CUSTOM+JERSEY+DAN+KAOS/@-3.3199293,114.5948147,14z/data=!4m15!1m8!3m7!1s0x2de4233f401afa0b:0x1d2398af08f43819!2sCUSTOM+JERSEY+DAN+KAOS!8m2!3d-3.3199293!4d114.6154141!10e5!16s%2Fg%2F11zd56j9f0!3m5!1s0x2de4233f401afa0b:0x1d2398af08f43819!8m2!3d-3.3199293!4d114.6154141!16s%2Fg%2F11zd56j9f0?entry=ttu&g_ep=EgoyMDI2MDkzMC4wIKXMDSoASAFQAw%3D%3D',

    // Koordinat workshop (pin presisi untuk embed peta & rute).
    'maps_latitude' => -3.3199293,
    'maps_longitude' => 114.6154141,

    'sizes' => ['S', 'M', 'L', 'XL', 'XXL', '3XL'],

    // Tambahan harga per ukuran (rupiah) yang tampil di nota & kalkulator.
    // Ukuran yang tidak terdaftar di sini tetap mengikuti pola (n-2) × 10.000
    // untuk nXL (4XL = 30.000, dst); selain itu 0. Harga per pesanan bisa
    // dioverride admin lewat kolom size_prices (detail pesanan).
    'size_surcharges' => [
        'XXL' => 10000,
        '3XL' => 20000,
        'XXXL' => 20000,
        '4XL' => 30000,
        '5XL' => 40000,
        '6XL' => 50000,
        '7XL' => 60000,
        '8XL' => 70000,
        '9XL' => 80000,
        '10XL' => 90000,
    ],

    // Biaya desain khusus jersey (dikenakan di awal, revisi tetap gratis).
    'design_fee_jersey' => 50000,

    /*
    |--------------------------------------------------------------------------
    | Pedoman Katalog Jersey 2026-2027 (harga, aturan order, upgrade)
    |--------------------------------------------------------------------------
    */

    'jersey' => [
        // Harga dasar Jersey Reguler (Atasan Only).
        'base_price' => 85000,

        // Spesifikasi dasar tanpa biaya tambahan.
        'base_spec' => ['Lengan Pendek', 'Kerah O-Neck', 'Bahan Dryfit Milano'],

        // Minimal pemesanan (pcs).
        'min_order' => 12,

        // Waktu produksi normal (hari kerja).
        'lead_time' => '14-25 hari kerja',

        // Opsi VIP ORDER — biaya tambahan per jersey untuk pengerjaan lebih cepat.
        'vip_fee' => 10000,

        // Biaya desain (1-2 hari kerja, revisi bebas hingga fix).
        'design_fee' => 50000,

        // DP minimal sebelum produksi (persen), wajib lunas sebelum pengiriman.
        'dp_percent' => 50,

        // Upgrade kerah: ['name' => nama, 'fee' => biaya tambahan per pcs].
        'collars' => [
            ['name' => 'O-Neck', 'fee' => 0],
            ['name' => 'V-Neck', 'fee' => 5000],
            ['name' => 'V-Neck Tumpuk', 'fee' => 5000],
            ['name' => 'O-Neck Variasi', 'fee' => 8000],
            ['name' => 'Double V-Neck', 'fee' => 8000],
            ['name' => 'Wangky Kancing', 'fee' => 12000],
            ['name' => 'Wangky Adidas Insert', 'fee' => 12000],
            ['name' => 'Wangky V-Neck', 'fee' => 12000],
            ['name' => 'Wangky Adidas', 'fee' => 12000],
            ['name' => 'Wangky Tali', 'fee' => 15000],
            ['name' => 'Wangky Zipper', 'fee' => 15000],
            ['name' => 'Shanghai Zipper', 'fee' => 15000],
        ],

        // Upgrade bahan: ['name' => nama, 'fee' => biaya tambahan per pcs].
        'fabrics' => [
            ['name' => 'Dryfit Milano', 'fee' => 0],
            ['name' => 'Brazil Bintik', 'fee' => 5000],
            ['name' => 'Benzema', 'fee' => 5000],
            ['name' => 'Embos Tofo', 'fee' => 15000],
            ['name' => 'Embos Straw', 'fee' => 15000],
            ['name' => 'Embos Labirin', 'fee' => 15000],
            ['name' => 'Embos Timnas', 'fee' => 15000],
            ['name' => 'Embos Geomatric', 'fee' => 15000],
            ['name' => 'Airwalk', 'fee' => 15000],
            ['name' => 'Jaqcuard Moving', 'fee' => 15000],
            ['name' => 'Jaqcuard Geomatric', 'fee' => 15000],
            ['name' => 'Jaqcuard Camo', 'fee' => 15000],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Jenis Sablon & Teknik (default — bisa diganti admin di /admin/pengaturan)
    |--------------------------------------------------------------------------
    */

    'services' => [
        [
            'title' => 'Sablon Plastisol',
            'desc' => 'Untuk kaos, polo, dan goodiebag. Warna cerah, detail tajam, dan tidak mudah retak — pilihan utama untuk desain banyak warna.',
        ],
        [
            'title' => 'Sablon DTF',
            'desc' => 'Untuk kaos, polo, dan goodiebag. Transfer print untuk desain detail dan gradasi warna, cocok untuk logo kompleks dengan jumlah kecil.',
        ],
        [
            'title' => 'Bordir',
            'desc' => 'Untuk PDH/kemeja, polo, dan jaket. Bordir logo dada, lengan, atau punggung sesuai request, dengan kesan rapi dan eksklusif.',
        ],
        [
            'title' => 'Printing Sublim — Jersey',
            'desc' => 'Full body untuk jersey futsal, sepak bola, dan basket. Bebas pilihan bahan dan kerah, plus nama & nomor pemain.',
        ],
        [
            'title' => 'Printing Sublim — Jaket',
            'desc' => 'Motif jaket custom menyatu dengan serat kain, tidak mudah mengelupas. Pilihan bahan mengikuti kebutuhan.',
        ],
        [
            'title' => 'Sablon Sublim Mug',
            'desc' => 'Cetak foto atau desain penuh pada mug, awet tidak luntur saat dicuci — cocok untuk hadiah dan merchandise.',
        ],
        [
            'title' => 'UV DTF — Tumbler',
            'desc' => 'Stiker UV awet dan tahan panas untuk tumbler custom satuan maupun partai banyak.',
        ],
        [
            'title' => 'Merchandise Lain',
            'desc' => 'Totebag, topi, goodie bag, dan merchandise lain menyesuaikan kebutuhan — konsultasikan ke CS.',
        ],
    ],

];
