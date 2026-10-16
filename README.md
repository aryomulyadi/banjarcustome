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
- Pesanan: form terstruktur (produk, jumlah + ukuran, jenis sablon, deadline, opsi **express** (same-day / di bawah 10 hari), pengiriman/alamat, upload file desain), honeypot anti-spam, rate limit 12 request/menit. Hint estimasi: kaos 3–7 hari kerja, jersey **14–25 hari kerja (VIP ORDER +Rp10.000/pcs, lebih cepat)**, skema **DP 50%**; **MOQ jersey 12 pcs divalidasi** (kolom jumlah — di bawah itu ditolak dgn pesan jelas) + link **Panduan Jersey**.
- Sukses: nomor order, ringkasan (estimasi + DP), tombol **kirim ringkasan ke admin via WhatsApp**.
- Tracking order (belum perlu login) via nomor order + WhatsApp, atau **link lacak token** `/lacak/{token}` (dipakai admin di template WA): riwayat tahap, deadline, unduh desain hasil, tombol **Setujui Desain** / **Minta Revisi**.
- Halaman: Layanan (termasuk estimasi produksi & pembayaran), **Panduan Jersey 2026-2027** (`/panduan-jersey`: harga dasar Rp85.000/pcs + spec dasar, MOQ 12 pcs, produksi 14–25 hari + VIP, DP 50%, biaya desain Rp50.000, tabel upgrade kerah & bahan, contoh perhitungan — di-sitemap & dilink dari produk berkategori jersey), Lokasi (`LocalBusiness`, embed koordinat presisi + link listing Google Maps), FAQ (`FAQPage`), Ukuran, Tentang, Kebijakan Privasi, Syarat & Ketentuan.
- `sitemap.xml`, `robots.txt`, meta SEO/OG/Twitter, `noindex` untuk halaman form/transaksi.

**Admin** (`/admin`, login dulu di `/login`)
- Dashboard: statistik + pesanan terbaru; **badge jumlah pesanan pending** di sidebar.
- Notifikasi **email pesanan baru** otomatis ke semua akun admin + `BC_ADMIN_EMAIL` di `.env` (bisa diubah kapan saja).
- Pesanan: filter status & pencarian, detail lengkap (ukuran/deadline/estimasi express/alamat/riwayat status), **cetak nota** (`/admin/pesanan/{id}/cetak`) dengan **tabel rincian per ukuran** (Jenis, Bahan, Type, Warna, Ukuran, Jumlah, Harga, Total Harga — S, M, L, … baris terpisah; lembar cetak bersih **tanpa nomor nota & tanpa status pesanan**), **unduh file desain**, **export Excel** (`laporan-pesanan.xlsx`, format identik dgn template impor), ubah status (riwayat status tersimpan).
- **Alur status terjaga**: Menunggu Konfirmasi → **Terkonfirmasi** → **Proses Desain** → **Siap Produksi** → Sedang Diproses → Selesai (+ Dibatalkan kapan saja **wajib alasan**); lompat tahap ditolak dengan pesan jelas; naik ke **Siap Produksi** otomatis dicek: desain disetujui, Σ ukuran = jumlah, harga ada, spesifikasi nota lengkap, DP diterima; select status hanya menampilkan langkah yang diizinkan; riwayat lengkap per transisi.
- **Desain & approval**: admin unggah hasil desain (disk privat; ganti file = reset persetujuan) + preview inline + "Tandai Disetujui (via WA)"; pelanggan **setujui / minta revisi** (alasan wajib, hitungan revisi tercatat) dari link lacak `/lacak/{token}`; unduh desain via token.
- **DP 50%**: kartu DP di detail — nominal + waktu diterima otomatis + sisa tagihan; kosongkan = penanda dihapus.
- **Kartu Kerja & SPK**: cetak **Kartu Kerja Produksi** (tabel ukuran, thumbnail desain, ttd) dan **SPK banner A4 lanskap** (strip BANJAR CUSTOME + pill SPK PRODUKSI, box jenis & deadline + badge Express, area desain final, panel rincian ukuran + **Total Keseluruhan** angka besar, baris JENIS/MODEL KERAH (jersey)/BAHAN/WARNA + catatan — **tanpa harga, WA, tanda tangan, nomor/tanggal/operator di cetakan**; nomor `SPK-{id}` tetap digenerate di DB saat cetak pertama).
- **Pesan WhatsApp per tahap**: 8 template (masuk, konfirmasi, desain, approval, revisi, nota+DP, DP diterima, selesai) terisi otomatis + link lacak — tombol Salin & Buka WhatsApp.
- **Timeline pelanggan**: halaman lacak menampilkan riwayat tahap (tanpa nama admin), deadline + badge **Lewat deadline**, unduh desain, kartu persetujuan; badge lewat deadline juga di daftar pesanan.
- **Biaya desain jersey**: Rp50.000 di awal (hint di kartu Harga + template WA nota; revisi tetap gratis) — ubah di `config/banjarcustom.php` → `design_fee_jersey`.
- **Impor riwayat pesanan Excel/CSV** (`/admin/pesanan/import`): template .xlsx **16 kolom** (`BULAN, customer, jenis, waktu pengambilan, type, bahan, warna, pesan bahan, produksi, finish, TOTAL, jumlah, ukuran, whatsapp, status, catatan`) — judul dicocokkan berdasar nama (urutan bebas, kolom ekstra diabaikan, wajib hanya `customer`), semua baris divalidasi dulu (1 baris error = tidak ada data masuk, transaksional), `jenis` divalidasi ketat ke daftar layanan (asing ditolak), nilai `type`/`bahan`/`warna` baru otomatis jadi **master data** + tercatat di laporan impor, flag `ok`/`nok`, **`BULAN` diturunkan jadi tanggal pesanan** (tgl 1 bulan tsb — "Oktober 2026"/"2026-10"; gagal parse = peringatan + tanggal impor), `ukuran` `S:6, M:12` → jumlah per ukuran (fallback jumlah), status key/label, badge **Impor** + filter Sumber.
- **Data Master** (`/admin/master-data`): CRUD **Type, Bahan, Warna** (tabel `options`, unik per jenis, swatch hex utk warna) — dipakai admin di kartu Spesifikasi & memvalidasi kolom impor; form pesanan publik tidak berubah.
- **Progres Produksi**: 3 penanda pelengkap status — **Pesan Bahan / Produksi / Finish** (`ok`/`nok`) diisi via kartu **Progres Produksi** di detail pesanan, tampil sebagai 3 titik di kolom **Progres** daftar pesanan, ikut ekspor/impor.
- **Harga pesanan**: admin mengisi harga satuan/total di detail pesanan (total terhitung otomatis dari rincian ukuran, atau satuan × jumlah tanpa rincian; peringatan bila total ≠ rincian) + kartu **Spesifikasi Nota** (Bahan/Type/Warna/**Model Kerah** — kolom `collar_model` utk jersey, ada datalist opsi dari Panduan Jersey) + **harga per ukuran** (`size_prices`, kosong = otomatis harga dasar + surcharge: XXL +10.000, 4XL +30.000, dst — ubah di `config/banjarcustom.php`); tampil di halaman detail, nota (Σ per baris + baris "Penyesuaian manual" bila ada selisih), dan export Excel — tidak ditampilkan ke pelanggan.
- **Kalkulator harga** (`/admin/kalkulator`): isian Nama Customer, Jenis, Bahan, Type, Warna + ukuran `S:10, M:12` → **tabel perhitungan baris per ukuran** identik dengan nota cetak, kolom Harga tiap baris bisa diedit (default = harga dasar + surcharge), biaya express, salin ringkasan + **Cetak Penawaran** (lembar penawaran tanpa nomor order). **Panel Jersey 2026-2027** muncul otomatis saat Jenis mengandung "jersey": select **Kerah** & **Bahan** (fee dari `config → banjarcustom.jersey`, default Rp85.000/pcs), checkbox **VIP ORDER (+Rp10.000/pcs)** & **Biaya desain (Rp50.000 sekali)** — menambah harga tiap baris/total otomatis, ringkasan & cetak penawaran menyebutnya.
- Produk, Kategori, Galeri: CRUD + pencarian; gambar lama dihapus aman setelah penggantian.
- FAQ, Testimoni, Pengaturan (jam buka, jenis sablon & teknik, slide & statistik beranda), Profil (ganti password dengan konfirmasi password saat ini).

**Keamanan & performa**
- Security headers (Content-Security-Policy: nonce + `'unsafe-eval'` untuk Alpine, whitelist `localhost`/`127.0.0.1:5173` di non-production karena Chrome menolak sumber `[::1]`/port-wildcard, X-Frame-Options, dll; HSTS hanya di production) — **termasuk di halaman error 404/403/500** (exception ditangkap middleware agar header tetap terpasang).
- Route fallback ke `errors/404`, halaman error 403/404/500 sendiri.
- Navigasi kategori di-cache (`nav.categories`), testi & slider di-cache per query.
- Gambar `loading="lazy"` + `width/height` (banner pertama `fetchpriority="high"`), banner dikompres (<300 KB).
- Design file disimpan di disk `private` (`storage/app/private/designs`) — tidak bisa diakses publik.
- Backup: `php artisan backup:run` membuat zip database SQLite + `storage/app/public` ke `storage/app/backups` (retensi 7; opsi `--keep` dan `--dir`). Dijadwalkan otomatis tiap **02:00** oleh Laravel scheduler; pasang cron `* * * * * cd /path/ke/projek && php artisan schedule:run` (atau cron langsung `0 2 * * * php artisan backup:run`).
- Aksesibilitas: skip-link "Lewati ke konten utama", label form terhubung (`for`/`id`), pesan error `role="alert"` + `aria-invalid`, `aria-live` di kalkulator, kontras amber light-theme dinaikkan ke amber-700 (lolos WCAG AA 4.5:1).
- CI: `.github/workflows/ci.yml` menjalankan `pint --test`, `composer test` (PHP 8.2 + SQLite), dan `npm run build` (Node 20) otomatis begitu repo di-push ke GitHub.

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
composer test                  # 244 test (unit + feature) - config:clear dulu
npm run build                  # build aset produksi
```

> Selalu pakai `composer test`, jangan `php artisan test` saat config cache
> (`php artisan config:cache`) masih aktif: env `DB_DATABASE=:memory:` dari
> `phpunit.xml` diabaikan dan suite akan gagal guard di `tests/TestCase.php`.

## Struktur penting

```
app/Http/Controllers/          # Publik: Home, Catalog, Order, Track, Page, Sitemap
app/Http/Controllers/Admin/    # Dashboard, Orders, Calculator, MasterData, Products,
                               # Categories, Galleries, Faq, Testimonial, Setting, Profile
app/Http/Middleware/           # SecurityHeaders (global, menangkap exception → header error)
app/Models/                    # CustomOrder (+ riwayat status, progres produksi), MasterOption
                               # (master Type/Bahan/Warna), Faq, Testimonial, Setting…
app/Support/HomeContent.php    # Sumber data slider & statistik beranda
app/Support/PriceCalculator.php # Harga satuan kalkulator (tangga qty murni, unit-test)
config/banjarcustom.php        # Kontak, jam buka, ukuran, link sosial, Google Maps
routes/web.php                 # Rute publik + sitemap/robots + fallback 404
routes/admin.php               # Semua rute admin (di-filter middleware auth+role)
database/migrations/           # Schema katalog/pesanan/riwayat/FAQ/testimoni/pengaturan
docs/PLAN-ALUR-PESANAN.md      # Alur pesanan (WA-first), designer/SPK, hybrid WA+website
resources/views/               # Layout publik, admin, errors, halaman konten
tests/Feature|Unit             # Uji form, tracking, admin CRUD, SEO/halaman
```

## Panduan deploy: shared hosting vs VPS

**Rekomendasi: shared hosting (cPanel)** — untuk website ini (SQLite, tanpa queue
besar, trafik profil/percakapan) shared hosting sudah lebih dari cukup dan termurah:

- Estimasi biaya: domain `.com` ±Rp150–200 rb/tahun + shared hosting ±Rp200–400 rb/tahun
  (paket PHP 8.2+) → total ±**Rp400–700 rb/tahun**. Pindah ke VPS (mis. Hetzner/DigitalOcean
  USD4–6/bulan + domain) hanya bila trafik naik atau butuh kontrol server penuh —
  itu berarti urus update OS, keamanan, dan SMTP sendiri.
- Syarat host: **PHP ≥ 8.2** dengan ekstensi `mbstring`, `dom`, `fileinfo`, `pdo_sqlite`,
  `sqlite3`, `zip`, `gd`; cron job; Let's Encrypt (HTTPS); folder `storage/` & `database/`
  writable; cukup memori (±128 MB).
- Mail: set `MAIL_MAILER=smtp` + kredensial di `.env` (pakai SMTP transaktional seperti
  Mailgun/SendGrid/IndiEmail yang disediakan host) supaya notifikasi pesanan baru terkirim.

Langkah umum (berlaku shared hosting maupun VPS — ikuti *Checklist sebelum deploy*):

1. Upload/buka repo di server, `composer install --no-dev --optimize-autoloader`
   (atau upload `vendor/` hasil `composer install` lokal bila host tanpa SSH/composer).
2. Salin `.env.example` → `.env`, isi `APP_URL`, `APP_KEY` (`php artisan key:generate`),
   `APP_ENV=production`, `APP_DEBUG=false`, kredensial SMTP, `BC_ADMIN_EMAIL`.
3. `php artisan migrate --force`, `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
4. `npm install && npm run build` (atau upload hasil build lokal: `public/build/` +
   `public/hot` **jangan ikut diunggah**).
5. `php artisan storage:link`; pastikan `database/database.sqlite` & `storage/` writable.
6. Cron (wajib agar backup 02:00 jalan): `* * * * * cd /path/ke/projek && php artisan schedule:run`.
7. Uji: buka situs, kirim1 form pesanan uji, login `/login`, cek `/admin/pesanan`.

## Checklist sebelum deploy produksi

- `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://domain-anda`
- `php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache`
- `npm run build` (aset sudah ada di `public/build`)
- `composer install --no-dev --optimize-autoloader`
- Ganti data placeholder di `.env` / `config/banjarcustom.php` (WhatsApp, jam buka, Google Maps)
- Pastikan `storage` dapat ditulis web server dan `public/storage` sudah di-`php artisan storage:link`
