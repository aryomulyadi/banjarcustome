# Planning Alur Pesanan → Produksi — Banjar Custome

Dokumen perencanaan alur operasional: bagaimana pesanan masuk (WhatsApp + form
website), melewati designer, menjadi nota dengan DP 50%, lalu masuk produksi
lewat **dua jalur berbeda** (konveksi kaos vs sublim jersey) — dan bagaimana
website menyatu dengan chat WhatsApp agar operasional tetap berkesinambungan.

Sebagian sudah diimplementasi (disebut *"sudah di sistem"*), sisanya masih
rencana.

---

## 1. Alur Operasional Nyata (WhatsApp-first)

### Jalur umum (semua pesanan)

| # | Tahap | Yang terjadi | Kanal |
|---|-------|--------------|-------|
| 1 | Masuk | Customer chat WA; admin membalas dan **meminta spesifikasi** (jenis, bahan, warna, jumlah, ukuran, deadline, kirim/ambil). Pesanan dari form website masuk sebagai notifikasi + badge di sistem. | WA + website |
| 2 | Spesifikasi fix | Admin memastikan semua input lengkap → pesanan dikonfirmasi di sistem (status naik). | website (WA tetap jalan) |
| 3 | **Desain** | Admin meneruskan ke **designer**, designer membuat desain, admin mengirim hasilnya ke customer. Customer minta **revisi berulang sampai fix**. | WA (file/preview) + website (status `design`) |
| 4 | Ukuran & **Nota** | Setelah desain fix, customer mengirim **daftar ukuran**. Admin membuat **nota: satu baris per ukuran** (Jumlah = qty ukuran; Harga = satuan + surcharge ukuran XXL +10rb, 4XL +30rb, dst; bisa diedit per pesanan), customer **DP 50%**. | website (nota cetak) + WA (kirim nota/tagih DP) |
| 5 | Pembelian bahan | DP masuk → admin membeli bahan (kaos, dll.) → masuk tahap produksi. | opsional (log di website) |

### Jalur A — Konveksi / Sablon (kaos, pdh, totebag, …)

```
Nota + DP 50% → Pembelian bahan → Cutting → Jahit → Sablon/Bordir → QC → Selesai
```

### Jalur B — Jersey / Sublim (berbeda!)

```
Desain fix → Designer membuat SPK Kerja → Langsung over ke OPERATOR SUBLIM
→ Proses sublim (print kain) → Jahit → QC → Selesai
```

Catatan penting jalur B: **tidak melewati tahap "pembelian bahan" sebagai gerbang
awal** — begitu desain fix, designer menerbitkan **SPK (Surat Perintah Kerja)**
yang langsung diteruskan ke operator sublim. SPK inilah serah terima resmi ke
produksi untuk jalur jersey.

---

## 2. Hybrid WhatsApp + Website (berkesinambungan)

Prinsip: **website = sumber kebenaran** (status, nota, DP, timeline),
**WA = kanal komunikasi & notifikasi**. Chat tidak menggantikan sistem; sistem
tidak menggantikan chat. Keduanya disambung oleh aturan sederhana:

1. Setiap kali admin membalas customer di WA, **admin juga meng-update status
   pesanan di website** (tombol Ubah Status, riwayat tersimpan otomatis).
2. Setiap perpindahan tahap penting punya **template pesan WA** yang tinggal
   dikirim (isi otomatis dari data pesanan + link lacak).
3. Customer tidak perlu login: cukup **link lacak** (token) untuk melihat
   status terkini, unduh desain, dan membuka nota.

### Template pesan WA per tahap

| Tahap | Kirim ke customer | Isi pesan (ringkas) |
|-------|-------------------|---------------------|
| Pesanan masuk | balasan pertama | Terima kasih + minta spesifikasi (jenis/bahan/warna/jumlah/ukuran/deadline/kirim) |
| Siap desain | customer | Pesanan dikonfirmasi, desain sedang dikerjakan designer |
| Desain selesai | customer | Lampiran/preview desain + link lacak + tombol Setujui / Minta Revisi |
| Revisi | customer | Perubahan yang dilakukan, minta konfirmasi ulang |
| Nota terbit | customer | Nota (cetak PDF dari sistem) + nominal **DP 50%** + metode bayar |
| DP diterima | customer | Terima kasih, pesanan masuk antrian produksi |
| Siap produksi | customer (opsional) | Bahan dibeli / SPK terbit, produksi dimulai |
| Selesai | customer | Tagih pelunasan 50% + jadwal ambil/kirim |

> Implementasi template ini menyusul (fase 9C) — hari ini admin menyalin
> manual; tombol "Salin ringkasan"/link WA sudah ada untuk pesanan baru.

---

## 3. Peta Status: Open → Done

| # | Status (usulan) | Key | Arti | Pemicu naik |
|---|-----------------|-----|------|-------------|
| 1 | Open | `pending` | Pesanan masuk, menunggu konfirmasi harga & spesifikasi | Customer submit form / impor admin |
| 2 | Konfirmasi | `confirmed` | Spesifikasi lengkap, siap diteruskan ke designer | Admin setujui + isi harga |
| 3 | Desain | `design` | Menunggu/dikerjakan designer; file dikirim untuk approval | Admin unggah/kirim desain |
| 4 | Siap Produksi | `ready` | Desain **fix**, daftar ukuran & nota terbit, DP 50% diterima | Customer setujui desain + DP masuk |
| 5 | Produksi | `production` | Jalur A: bahan dibeli & dikerjakan; Jalur B: SPK over ke operator sublim | Serah terima (kartu kerja / SPK) |
| 6 | Selesai | `completed` | QC lolos, pelunasan beres, siap diambil/dikirim | Admin tandai selesai |
| — | Dibatalkan | `cancelled` | Kapan pun, wajib alasan (`cancel_reason`) | Admin/customer |

Status saat ini di sistem: `pending → production → completed/cancelled`
(+ audit trail `order_status_history`). Tiga key baru (`confirmed`, `design`,
`ready`) ditambahkan di fase 9A.

### Matriks transisi (siapa boleh ubah)

| Dari \ Ke | confirmed | design | ready | production | completed | cancelled |
|-----------|-----------|--------|-------|------------|-----------|-----------|
| pending | admin | — | — | — | — | admin |
| confirmed | — | admin | — | — | — | admin |
| design | — | — | admin* | — | — | admin |
| ready | — | **revisi** | — | admin | — | admin |
| production | — | — | — | — | admin | admin |
| completed | — | — | — | — | — | admin |

\* hanya setelah persetujuan customer tercatat. Transisi lompat ditolak dengan
pesan jelas. Setiap transisi menulis `order_status_history`; `cancelled` wajib
beralasan.

---

## 4. Nota & Harga Per Ukuran *(sudah di sistem)*

- **Satu baris per ukuran**: kolom Jenis/Bahan/Type/Warna hanya di baris
  pertama, lalu Ukuran, Jumlah (qty ukuran), Harga, Total Harga.
- **Surcharge default** (`config/banjarcustom.size_surcharges`): XXL +10.000,
  3XL/XXXL +20.000, 4XL +30.000, … (pola `(n−2) × 10.000`); bisa diubah di
  config.
- **Override per pesanan**: kolom `size_prices` (JSON) di `custom_orders` —
  admin mengisi harga khusus per ukuran di detail pesanan; kosong = otomatis
  (harga dasar + surcharge).
- **Subtotal = Σ baris**; bila admin menyetel total manual ≠ Σ → baris
  **"Penyesuaian manual"** tampil di nota agar angka transparan.
- **Kalkulator** (`/admin/kalkulator`): input `S:10, M:12` → tabel perhitungan
  multi-baris identik dengan nota, harga tiap baris bisa diedit; "Cetak
  Penawaran" menghasilkan lembar penawaran tanpa nomor order.
- Nota cetak **tidak lagi memuat** Deadline/Estimasi/Pengiriman/Alamat —
  informasi itu tetap ada di halaman detail pesanan.

---

## 5. Deadline & SLA

- Satu tanggal **deadline** (janji serah) tampil di detail & timeline pelanggan.
- SLA internal (usulan, dihitung mundur dari deadline):

  | Tahap | Batas waktu |
  |-------|-------------|
  | Konfirmasi (Open → Confirmed) | ≤ 1 hari kerja sejak masuk |
  | Desain (Confirmed → Design) | ≤ 2 hari kerja |
  | Approval desain | ≤ 2 hari (lewat → follow-up WA) |
  | Setelah desain fix → nota + DP | ≤ 1 hari kerja |
  | Produksi (Ready → Production) | mulai ≤ 1 hari setelah DP |
  | QC + Selesai | ≥ 3 hari sebelum deadline |

- Flag **"Lewat deadline"** di daftar admin: `deadline < hari ini && status
  not in (completed, cancelled)`. Zona `Asia/Makassar`.

---

## 6. Alur Desain & Approval

1. Customer mengunggah referensi desain bersama pesanan (sudah ada, disk
   private).
2. Designer membuat desain; hasilnya **diunggah admin** (field
   `admin_design_file` — menyusul fase 9D) atau dikirim via WA.
3. Lewat tombol "Kirim Desain ke Customer": WA berisi link lacak → customer
   melihat desain + tombol **Setujui** / **Minta Revisi** (alasan wajib).
4. `design → ready` hanya setelah customer menekan **Setujui**. Revisi kembali
   ke `design` (usulan: 2× gratis, selebihnya biaya dikonfirmasi manual).
5. Syarat naik `ready` (dicek otomatis): persetujuan desain, `size_quantities`
   terisi & jumlah per ukuran = `quantity`, harga tersedia, spesifikasi nota
   (bahan/type/warna) terisi, **nota terbit & DP 50% diterima**.

---

## 7. Serah Terima ke Produksi

### Jalur A — Kartu Kerja Produksi (kaos/konveksi)

- **B+C dulu** (fase 9C):
  - **B. Cetak Kartu Kerja**: tombol di detail pesanan — No. order, customer,
    spesifikasi, **tabel ukuran per baris**, deadline, thumbnail desain,
    catatan. Fisik = sumber kebenaran di meja produksi.
  - **C. Kirim ringkasan ke grup WA produksi** (pendukung).
- **A. Role `produksi` + halaman antrian** menyusul bila volume > ~15
  pesanan/hari atau tim sudah punya akun.

### Jalur B — SPK Kerja (jersey/sublim)

- Begitu desain fix: designer/admin **mencetak SPK** dari lembar yang sama
  (Kartu Kerja + nomor SPK + tanggal + operator sublim yang dituju) lalu
  **langsung over ke operator sublim** — tanpa gerbang "pembelian bahan".
- Status `ready → production` dicatat saat SPK diserahkan; nama operator bisa
  dicatat di `notes`/riwayat (penambahan kolom `assigned_operator` opsional).
- Perbedaan A vs B: A menunggu bahan dibeli; B menunggu SPK terbit.

---

## 8. Tracking & Transparansi Pelanggan

- Halaman lacak menampilkan **timeline dari `order_status_history`** (tahap +
  waktu, tanpa nama admin) + deadline + unduh desain + (opsional) lihat nota.
- Notifikasi peralihan tahap penting via WA otomatis memakai link lacak.
- Email notifikasi pesanan baru sudah ada untuk admin.

---

## 9. Tahap Implementasi

| Fase | Isi | Status |
|------|-----|--------|
| 9A | Tambah status `confirmed`/`design`/`ready` + guard transisi + alasan batal | **selesai** |
| 9B | Form Spesifikasi Produksi (validasi ukuran = qty, syarat naik status, cek DP) | **selesai** |
| 9C | Cetak Kartu Kerja + **SPK jalur sublim** + template pesan WA per tahap (§2) | **selesai** |
| 9D | Upload desain admin + approval customer (setujui/revisi via link lacak) | **selesai** |
| 9E | Flag lewat deadline + SLA per tahap di daftar admin | **selesai** |
| 9F | Timeline riwayat di halaman lacak pelanggan | **selesai** |
| 9G | (opsional) Role `produksi` + halaman antrian / `assigned_operator` | **selesai** (Fase 17, prd.txt) |
| — | Nota baris per ukuran + surcharge + `size_prices` + kalkulator multi-baris | **selesai** |

Urutan disarankan: 9A → 9B → 9E → 9C → 9F → 9D → 9G.

Setiap fase wajib melewati: `vendor/bin/pint`, `composer test`,
`npm run build`, probe Edge (0 pesan console), lalu catat ke `prd.txt`.

---

## 10. Keputusan yang Menunggu Review

1. Nama status Indonesia `confirmed`/`ready` (usulan: "Terkonfirmasi" /
   "Siap Produksi").
2. Batas revisi desain (usulan 2×) & aturan biaya revisi.
3. Apakah DP 50% harus dicatat nominalnya di sistem (kolom `dp_amount`) atau
   cukup ditandai "DP diterima"?
4. Kolom `assigned_operator` untuk jalur sublim, atau cukup catatan teks?
5. Batas waktu approval desain otomatis via WA (template follow-up) — kirim
   otomatis atau manual?
