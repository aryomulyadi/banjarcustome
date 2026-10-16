<x-layouts.admin :title="'Impor Pesanan'">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Kembali ke daftar pesanan
        </a>
        <a
            href="{{ route('admin.orders.import.template') }}"
            class="inline-flex h-9 items-center gap-2 rounded-md border border-border px-3 text-sm font-medium transition-colors hover:bg-secondary"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
            Unduh Template
        </a>
    </div>

    @isset($report)
        @if ($report['fatal'])
            <div class="mt-4 rounded-xl border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive" role="alert">
                {{ $report['fatal'] }}
            </div>
        @endif

        @if ($report['errors'] !== [])
            <div class="mt-4 rounded-xl border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive" role="alert">
                <p class="font-semibold">Impor dibatalkan — {{ count($report['errors']) }} baris tidak valid. Tidak ada data yang masuk ke database.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($report['errors'] as $error)
                        <li>Baris {{ $error['baris'] }}: {{ $error['pesan'] }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($report['imported'] !== null)
            <div class="mt-4 rounded-xl border border-emerald-600/40 bg-emerald-600/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400" role="status">
                <p>Berhasil mengimpor {{ $report['imported'] }} pesanan. Semua baris divalidasi dan tersimpan dalam satu transaksi.</p>

                @if (! empty($report['created']))
                    <p class="mt-2 font-semibold">Master data baru dibuat otomatis ({{ count($report['created']) }}):</p>
                    <ul class="mt-1 list-disc space-y-0.5 pl-5">
                        @foreach ($report['created'] as $master)
                            <li>{{ $master['kind'] === 'type' ? 'Type' : ($master['kind'] === 'material' ? 'Bahan' : 'Warna') }}: {{ $master['name'] }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @if (! empty($report['warnings']))
            <div class="mt-4 rounded-xl border border-amber-600/40 bg-amber-600/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-400" role="status">
                <p class="font-semibold">Perhatian ({{ count($report['warnings']) }}):</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    @foreach ($report['warnings'] as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    @endisset

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        <div class="rounded-xl border border-border bg-card p-5 lg:col-span-2">
            <h2 class="text-base font-semibold">Unggah File</h2>
            <p class="mt-1.5 text-sm text-muted-foreground">
                Format <strong>.xlsx</strong> atau <strong>.csv</strong>, maksimal 2 MB. Baris pertama harus baris judul kolom sesuai template.
            </p>

            <form method="POST" action="{{ route('admin.orders.import.store') }}" enctype="multipart/form-data" class="mt-4 space-y-4">
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
                        Kosongkan baris yang tidak berisi data. Kolom boleh dalam urutan apa pun asalkan judulnya benar.
                    </p>
                </div>

                <x-button type="submit">Validasi &amp; Impor</x-button>
            </form>

            <p class="mt-4 rounded-lg bg-secondary/60 px-3 py-2 text-xs leading-relaxed text-muted-foreground">
                Judul kolom dicocokkan berdasar nama (boleh urutan bebas, kolom ekstra diabaikan, wajib hanya <strong>customer</strong>).
                Semua baris divalidasi lebih dulu — bila ada satu baris error, tidak ada data yang diimpor; perbaiki file lalu unggah ulang.
            </p>
        </div>

        <div class="rounded-xl border border-border bg-card p-5">
            <h2 class="text-base font-semibold">Kolom Template</h2>
            <ul class="mt-3 space-y-2 text-sm">
                <li><span class="font-medium">customer*</span> — nama pelanggan, maks 255 karakter</li>
                <li><span class="font-medium">BULAN</span> — bulan pesanan, mis. "Oktober 2026" / "2026-10" / "10/2026"; diturunkan jadi tanggal pesanan (tgl 1), gagal parse = tanggal impor + peringatan</li>
                <li><span class="font-medium">jenis</span> — harus terdaftar di Pengaturan → Jenis Sablon &amp; Teknik (asing = ditolak)</li>
                <li><span class="font-medium">waktu pengambilan</span> — deadline, d/m/Y atau Y-m-d</li>
                <li><span class="font-medium">type / bahan / warna</span> — teks bebas; nilai baru otomatis jadi master data (tercatat di laporan)</li>
                <li><span class="font-medium">pesan bahan / produksi / finish</span> — <code>ok</code> atau <code>nok</code> (juga ya/tidak/1/0; kosong = nok)</li>
                <li><span class="font-medium">TOTAL</span> — total harga, angka polos atau pemisah ribuan (1.500.000); desimal &amp; teks ditolak</li>
                <li><span class="font-medium">jumlah</span> — pcs, bilangan bulat (bila kosong diisi jumlah dari kolom ukuran)</li>
                <li><span class="font-medium">ukuran</span> — per ukuran, format "S:6, M:12" (maks 30 baris)</li>
                <li><span class="font-medium">whatsapp</span> — minimal 5 angka bila diisi</li>
                <li><span class="font-medium">status</span> — pending / production / completed / cancelled (label juga diterima; kosong = pending)</li>
                <li><span class="font-medium">catatan</span> — maks 1000 karakter</li>
            </ul>
            <p class="mt-3 text-xs text-muted-foreground">
                * wajib diisi. Judul kolom dicocokkan tanpa memperhatikan huruf besar/kecil dan urutan — kolom ekstra diabaikan.
                Pesanan hasil impor ditandai badge <strong>Impor</strong> di daftar pesanan.
            </p>
        </div>
    </div>

</x-layouts.admin>
