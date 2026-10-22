<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Terjemahan Pesan Validasi (Bahasa Indonesia)
    |--------------------------------------------------------------------------
    |
    | Dipakai oleh form publik & admin. Kunci yang tidak dicantumkan di sini
    | otomatis jatuh ke fallback locale (en) — aman ditambah bertahap.
    |
    */

    'accepted' => ':attribute harus diterima.',
    'array' => ':attribute harus berupa daftar.',
    'boolean' => ':attribute harus bernilai ya atau tidak.',
    'confirmed' => 'Konfirmasi :attribute tidak cocok.',
    'date' => ':attribute harus berupa tanggal yang valid.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'extensions' => ':attribute harus memiliki ekstensi: :values.',
    'file' => ':attribute harus berupa berkas.',
    'image' => ':attribute harus berupa berkas gambar (jpg, png, webp).',
    'in' => ':attribute tidak valid.',
    'integer' => ':attribute harus berupa bilangan bulat.',
    'max' => [
        'array' => ':attribute tidak boleh berisi lebih dari :max item.',
        'file' => ':attribute tidak boleh lebih besar dari :max kilobyte.',
        'numeric' => ':attribute tidak boleh lebih besar dari :max.',
        'string' => ':attribute tidak boleh lebih dari :max karakter.',
    ],
    'mimes' => ':attribute harus berkas bertipe: :values.',
    'mimetypes' => ':attribute harus berkas bertipe: :values.',
    'min' => [
        'array' => ':attribute minimal berisi :min item.',
        'file' => ':attribute minimal :min kilobyte.',
        'numeric' => ':attribute minimal :min.',
        'string' => ':attribute minimal :min karakter.',
    ],
    'nullable' => ':attribute boleh dikosongkan.',
    'numeric' => ':attribute harus berupa angka.',
    'password' => [
        'letters' => 'Password harus mengandung minimal satu huruf.',
        'mixed' => 'Password harus mengandung huruf besar dan kecil.',
        'numbers' => 'Password harus mengandung minimal satu angka.',
        'symbols' => 'Password harus mengandung minimal satu simbol.',
        'uncompromised' => 'Password ditemukan pada kebocoran data — silakan gunakan password lain.',
        'min' => 'Password minimal :min karakter.',
    ],
    'regex' => ':attribute tidak valid.',
    'required' => ':attribute wajib diisi.',
    'size' => [
        'array' => ':attribute harus berisi tepat :size item.',
        'file' => ':attribute harus berukuran tepat :size kilobyte.',
        'numeric' => ':attribute harus bernilai tepat :size.',
        'string' => ':attribute harus berisi tepat :size karakter.',
    ],
    'string' => ':attribute harus berupa teks.',
    'unique' => ':attribute sudah digunakan.',

    'custom' => [
        'whatsapp_number' => [
            'regex' => 'Nomor WhatsApp hanya boleh berisi angka, spasi, tanda +, - dan kurung.',
        ],
        'colors.*.hex' => [
            'regex' => 'Kode warna harus berformat heksadesimal, mis. #1A2B3C.',
        ],
    ],

    'attributes' => [
        'address' => 'alamat',
        'answer' => 'jawaban',
        'category_id' => 'kategori',
        'colors' => 'warna',
        'content' => 'isi testimoni',
        'current_password' => 'password saat ini',
        'deadline' => 'deadline',
        'design_file' => 'berkas desain',
        'design_file_admin' => 'berkas desain',
        'delivery_method' => 'metode pengambilan/pengiriman',
        'email' => 'email',
        'fee' => 'biaya',
        'file' => 'berkas',
        'hex' => 'kode warna',
        'image' => 'gambar',
        'images' => 'gambar',
        'kind' => 'jenis data',
        'label' => 'label',
        'min_qty' => 'minimal jumlah',
        'name' => 'nama',
        'order_id' => 'nomor pesanan',
        'password' => 'password',
        'position' => 'urutan',
        'price_tiers' => 'tier harga',
        'product_id' => 'produk',
        'question' => 'pertanyaan',
        'quantity' => 'total jumlah',
        'rating' => 'rating',
        'revision_reason' => 'alasan revisi',
        'service_type' => 'jenis sablon / pengerjaan',
        'services' => 'layanan',
        'sizes' => 'jumlah ukuran',
        'slides' => 'slide banner',
        'stats' => 'statistik',
        'status' => 'status',
        'subtitle' => 'subjudul',
        'title' => 'judul',
        'unit_price' => 'harga satuan',
        'value' => 'nilai',
        'whatsapp_number' => 'nomor WhatsApp',
        'website' => 'website',
    ],

];
