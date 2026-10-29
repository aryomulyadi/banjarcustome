<x-layouts.admin :title="'Impor Data Jersey'">

    <a href="{{ route('admin.jersey.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Kembali ke Data Jersey
    </a>

    @include('admin.partials.import-report', ['report' => $report ?? null])

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <div class="rounded-xl border border-border bg-card p-5 lg:col-span-2">
            <h2 class="text-base font-semibold">Unggah File</h2>
            <p class="mt-1.5 text-sm text-muted-foreground">
                Format <strong>.xlsx</strong> atau <strong>.csv</strong>, maksimal 2 MB. Baris pertama adalah baris judul kolom —
                <strong>template resmi tidak wajib</strong>; file laporan asli dengan judul alternatif (mis. "TGL MASUK",
                "Nama/No. Customer", "Designer") langsung bisa diimpor.
            </p>

            <form method="POST" action="{{ route('admin.jersey.import.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label for="import-file" class="text-sm font-medium">File spreadsheet</label>
                    <input
                        id="import-file"
                        type="file"
                        name="file"
                        accept=".xlsx,.csv"
                        required
                        aria-describedby="import-file-hint"
                        class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors file:mr-3 file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                    @error('file')
                        <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                    @enderror
                    <p id="import-file-hint" class="text-xs text-muted-foreground">
                        Kosongkan baris yang tidak berisi data. Kolom boleh urutan apa pun; kolom tak dikenal (NO, SISA HARI) diabaikan.
                    </p>
                </div>

                <x-button type="submit">Validasi &amp; Impor</x-button>
            </form>

            <p class="mt-4 rounded-lg bg-secondary/60 px-3 py-2 text-xs leading-relaxed text-muted-foreground">
                Semua baris divalidasi lebih dulu — bila ada satu baris error, tidak ada data yang diimpor; perbaiki file lalu unggah ulang.
            </p>
        </div>

        <div class="rounded-xl border border-border bg-card p-5">
            <h2 class="text-base font-semibold">Kolom yang Dikenali</h2>
            <ul class="mt-3 space-y-2 text-sm">
                <li><span class="font-medium">tanggal masuk*</span> — d/m/Y, Y-m-d, atau "24/10" (tahun berjalan) (alias: tgl masuk, tanggal, date)</li>
                <li><span class="font-medium">customer*</span> — nama/no. customer, maks 160 karakter (alias: nama customer, pelanggan)</li>
                <li><span class="font-medium">desainer*</span> — dari Data Master; nilai baru otomatis jadi master (alias: designer)</li>
                <li><span class="font-medium">deadline</span> — d/m/Y, Y-m-d, "24/10", atau "24-Oct"; teks bukan-tanggal diabaikan + peringatan (alias: waktu pengambilan, batas waktu)</li>
                <li><span class="font-medium">jenis kerah / jenis bahan / model</span> — dari Data Master; nilai baru otomatis jadi master (alias: kerah, bahan)</li>
                <li><span class="font-medium">status</span> — Proses Desain / Revisi / Layout / Printing / Jahit / Done / Cancel; alias file lama: Pending &amp; Belum Mulai → Proses Desain, Masuk Printing → Printing, Selesai → Done, Batal → Cancel (kosong = Proses Desain)</li>
                <li><span class="font-medium">catatan</span> — maks 2000 karakter (alias: note, keterangan)</li>
            </ul>
            <p class="mt-3 text-xs text-muted-foreground">
                * wajib diisi (baris pertama). <strong>Baris lanjutan</strong>: kolom wajib yang
                dikosongkan diwariskan dari baris di atasnya — diperingatkan di laporan.
                Kolom <strong>NO</strong> &amp; <strong>SISA HARI</strong> diabaikan.
                Judul dicocokkan tanpa memperhatikan huruf besar/kecil.
            </p>
        </div>
    </div>

</x-layouts.admin>
