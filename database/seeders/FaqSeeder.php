<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faqs = [
            [
                'question' => 'Berapa lama proses produksinya?',
                'answer' => 'Kaos umumnya selesai 3-7 hari kerja dan jersey 10-12 hari, dihitung setelah desain disetujui. Butuh lebih cepat? Tersedia pengerjaan express - kaos bisa same-day dan pesanan lain di bawah 10 hari dengan biaya tambahan, konfirmasi dulu ke CS.',
            ],
            [
                'question' => 'Ada minimal pesanan tidak?',
                'answer' => 'Bisa pesan satuan maupun grosir. Untuk jumlah banyak tersedia harga khusus - tanyakan ke CS untuk penawaran terbaiknya.',
            ],
            [
                'question' => 'Bisa kirim desain sendiri?',
                'answer' => 'Sangat bisa. Kirim file AI, PSD, PNG, atau JPG resolusi tinggi lewat form pesanan atau WhatsApp. Belum punya desain? Tim kami bisa bantu dari nol.',
            ],
            [
                'question' => 'Apa saja jenis sablon yang tersedia?',
                'answer' => 'Untuk kaos, polo, dan goodiebag tersedia sablon Plastisol dan DTF; untuk kemeja/PDH/polo/jaket bisa bordir sesuai request; jersey memakai printing sublim dengan pilihan bahan dan kerah; mug dengan sablon sublim; tumbler dengan UV DTF; merchandise lain menyesuaikan kebutuhan.',
            ],
            [
                'question' => 'Bagaimana cara pesannya?',
                'answer' => 'Isi form pesanan atau chat WhatsApp, lalu kirim/brief desain. Kami kirim mockup untuk persetujuan, setelah itu bayar DP 50% dan produksi dimulai. Pesanan dikirim atau diambil di workshop.',
            ],
            [
                'question' => 'Bagaimana skema pembayarannya?',
                'answer' => 'Skema DP 50% di awal untuk mulai produksi, lalu 50% sisanya dibayar sebelum pesanan diambil atau dikirim. Untuk pesanan express, detail biaya disepakati bersama CS sebelum produksi dimulai.',
            ],
            [
                'question' => 'Bisa kirim ke luar Banjarmasin?',
                'answer' => 'Bisa. Pengiriman ke seluruh Indonesia lewat ekspedisi, dan untuk area Banjarmasin bisa diambil sendiri di workshop.',
            ],
            [
                'question' => 'Bagaimana cara cek status pesanan?',
                'answer' => 'Buka menu Cek Status Pesanan, lalu masukkan nomor order beserta nomor WhatsApp yang dipakai saat order. Tanpa perlu login.',
            ],
            [
                'question' => 'Ukuran tersedia apa saja?',
                'answer' => 'Ukuran kaos tersedia S sampai 3XL - lihat panduan ukuran untuk detail lingkar dada dan panjangnya. Untuk jersey dan kerah khusus tersedia pilihan bahan lain.',
            ],
            [
                'question' => 'Kalau hasilnya tidak sesuai bagaimana?',
                'answer' => 'Setiap pesanan melewati QC sebelum dikirim. Jika ada yang kurang pas, kirim fotonya via WhatsApp sesegera mungkin setelah pesanan diterima, nanti kami carikan solusinya.',
            ],
        ];

        foreach ($faqs as $index => $faq) {
            // updateOrCreate: jalankan ulang seeder untuk memperbarui jawaban default
            // (editan manual admin di /admin/faq akan tertimpa bila seeder dijalankan lagi).
            Faq::updateOrCreate(
                ['question' => $faq['question']],
                [
                    'answer' => $faq['answer'],
                    'position' => $index + 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
