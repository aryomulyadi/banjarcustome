# Banjar Custome — Website Katalog & Order Custom Sablon

Website company profile + pemesanan custom (sablon kaos/dll) berbasis **Laravel 12**, dengan panel admin, katalog produk, form pesanan terstruktur, tracking order via WhatsApp, serta SEO & performa yang sudah dioptimalkan.

## Stack

| Bagian | Teknologi |
| --- | --- |
| Backend | Laravel 12 (PHP 8.2) |
| Database | SQLite (default; bisa MySQL/Postgres lewat `.env`) |
| Frontend | Blade + Tailwind CSS v4 + Alpine.js + Vite 7 |
| Assets | `resources/css/app.css`, `resources/js/app.js` → `npm run build` |
| Autentikasi | Manual (guard `web`, hashing bcrypt), tanpa Breeze |

## Fitur

**Publik**
- Beranda: slider promo (bisa pause, hormati `prefers-reduced-motion`), galeri, testimoni, statistik.
- Katalog produk: pencarian, filter kategori & warna, pagination (token tema).
- Detail produk: JSON-LD `Product`, estimasi harga, pilihan warna.
- Pesanan: form terstruktur (produk, jumlah + ukuran, jenis sablon, deadline, opsi **express** (same-day / di bawah 10 hari), pengiriman/alamat, upload file desain), honeypot anti-spam, rate limit 12 request/menit. Hint estimasi: kaos 3–7 hari kerja, jersey 10–12 hari, skema **DP 50%**.
- Sukses: nomor order, ringkasan (estimasi + DP), tombol **kirim ringkasan ke admin via WhatsApp**.
- Tracking order (belum perlu login) via nomor order + WhatsApp.
- Halaman: Layanan (termasuk estimasi produksi & pembayaran), Lokasi (`LocalBusiness`, embed koordinat presisi + link listing Google Maps), FAQ (`FAQPage`), Ukuran, Tentang, Kebijakan Privasi, Syarat & Ketentuan.
- `sitemap.xml`, `robots.txt`, meta SEO/OG/Twitter, `noindex` untuk halaman form/transaksi.

**Admin** (`/admin`, login dulu di `/login`)
- Dashboard: statistik + pesanan terbaru; **badge jumlah pesanan pending** di sidebar.
- Notifikasi **email pesanan baru** otomatis ke semua akun admin + `BC_ADMIN_EMAIL` di `.env` (bisa diubah kapan saja).
- Pesanan: filter status & pencarian, detail lengkap (ukuran/deadline/estimasi express/alamat/riwayat status), **cetak nota** (`/admin/orders/{id}/print`), **unduh file desain**, **export CSV** (kolom Estimasi), ubah status (riwayat status tersimpan).
- Produk, Kategori, Galeri: CRUD + pencarian; gambar lama dihapus aman setelah penggantian.
- FAQ, Testimoni, Pengaturan (jam buka, jenis sablon & teknik, slide & statistik beranda), Profil (ganti password dengan konfirmasi password saat ini).

**Keamanan & performa**
- Security headers (Content-Security-Policy: nonce + `'unsafe-eval'` untuk Alpine, whitelist `localhost`/`127.0.0.1:5173` di non-production karena Chrome menolak sumber `[::1]`/port-wildcard, X-Frame-Options, dll; HSTS hanya di production).
- Route fallback ke `errors/404`, halaman error 403/404/500 sendiri.
- Navigasi kategori di-cache (`nav.categories`), testi & slider di-cache per query.
- Gambar `loading="lazy"` + `width/height` (banner pertama `fetchpriority="high"`), banner dikompres (≤300 KB).
- Design file disimpan di disk `private` (`storage/app/private/designs`) — tidak bisa diakses publik.
- Backup: `php artisan backup:run` membuat zip database SQLite + `storage/app/public` ke `storage/app/backups` (retensi 7; opsi `--keep` dan `--dir`). Contoh cron harian: `0 2 * * * cd /path/ke/projek && php artisan backup:run`.

## Menjalankan

```bash
composer setup          # install + .env + key + migrate + npm build
composer dev            # serve + queue + pail logs + vite (paralel)
```

Manual:

```bash
composer install
copy .env.example .env          # Windows: copy, macOS/Linux: cp
php artisan key:generate
php artisan migrate
php artisan db:seed             # data awal + akun admin@banjarcustom.test / password
npm install && npm run build
php artisan serve
```

## Perintah yang dipakai untuk pengecekan

```bash
vendor/bin/pint                # format kode (laravel preset)
composer test                  # 132 test (unit + feature) - config:clear dulu
npm run build                  # build aset produksi
```

> Selalu pakai `composer test`, jangan `php artisan test` saat config cache
> (`php artisan config:cache`) masih aktif: env `DB_DATABASE=:memory:` dari
> `phpunit.xml` diabaikan dan suite akan gagal guard di `tests/TestCase.php`.

## Struktur penting

```
app/Http/Controllers/          # Publik: Home, Catalog, Order, Track, Page, Sitemap
app/Http/Controllers/Admin/    # Dashboard, Orders, Products, Categories, Galleries,
                               # Faq, Testimonial, Setting, Profile
app/Http/Middleware/           # SecurityHeaders (global)
app/Models/                    # CustomOrder (+ riwayat status), Faq, Testimonial, Setting…
app/Support/HomeContent.php    # Sumber data slider & statistik beranda
config/banjarcustom.php        # Kontak, jam buka, ukuran, link sosial, Google Maps
routes/web.php                 # Rute publik + sitemap/robots + fallback 404
routes/admin.php               # Semua rute admin (di-filter middleware auth+role)
database/migrations/           # Schema katalog/pesanan/riwayat/FAQ/testimoni/pengaturan
resources/views/               # Layout publik, admin, errors, halaman konten
tests/Feature|Unit             # Uji form, tracking, admin CRUD, SEO/halaman
```

## Checklist sebelum deploy produksi

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda`
- `php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache`
- `npm run build` (aset sudah ada di `public/build`)
- `composer install --no-dev --optimize-autoloader`
- Ganti data placeholder di `.env` / `config/banjarcustom.php` (WhatsApp, jam buka, Google Maps)
- Pastikan `storage` dapat ditulis web server dan `public/storage` sudah di-`php artisan storage:link`
