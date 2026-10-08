<x-layouts.app
    :title="'Layanan Konveksi & Sablon — Banjar Custome'"
    :description="'Layanan Banjar Custome: sablon Plastisol dan DTF untuk kaos, polo, dan goodiebag, bordir untuk kemeja/polo/jaket, printing sublim jersey dan jaket, sublim mug, UV DTF tumbler, hingga merchandise. Alur produksi jelas dari order hingga kirim.'"
>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span aria-hidden="true">/</span>
            <span class="text-foreground" aria-current="page">Layanan</span>
        </nav>

        <span class="text-xs font-semibold uppercase tracking-widest text-primary">Layanan Kami</span>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Layanan Konveksi &amp; Sablon</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-muted-foreground">
            Dari satu pcs hingga ratusan pcs — kami tangani desain, sablon, jahit, sampai pengemasan.
            Pilih teknik sablon sesuai kebutuhan desain dan budget tim Anda.
        </p>

        <h2 class="mt-10 text-xl font-bold sm:text-2xl">Jenis Sablon &amp; Teknik</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($services as $service)
                <div class="rounded-xl border border-border bg-card p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/15 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z"/></svg>
                    </span>
                    <h3 class="mt-3 font-semibold">{{ $service['title'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">{{ $service['desc'] ?? '' }}</p>
                </div>
            @endforeach
        </div>

        <h2 class="mt-12 text-xl font-bold sm:text-2xl">Produk yang Kami Tangani</h2>
        <div class="mt-5 flex flex-wrap gap-2.5">
            @foreach (['Kaos Polos', 'Kaos Sablon', 'Jersey Printing', 'Kemeja', 'Jaket', 'Seragam Sekolah', 'Seragam Kantor', 'Hoodie', 'Totebag', 'Merchandise Komunitas'] as $item)
                <span class="rounded-full border border-border bg-card px-4 py-2 text-sm font-medium">{{ $item }}</span>
            @endforeach
        </div>

        <h2 class="mt-12 text-xl font-bold sm:text-2xl">Estimasi Produksi &amp; Pembayaran</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            @foreach ([
                ['title' => 'Kaos — 3–7 hari kerja', 'desc' => 'Estimasi produksi kaos sablon reguler, dihitung setelah desain disetujui.'],
                ['title' => 'Jersey — 10–12 hari', 'desc' => 'Jersey printing sublim butuh waktu lebih lama karena proses jahit dan cetak full body.'],
                ['title' => 'Express — same-day / di bawah 10 hari', 'desc' => 'Butuh lebih cepat? Kaos bisa dikerjakan hari itu juga dan pesanan lain di bawah 10 hari, dengan biaya tambahan'.(config('banjarcustom.express_fee') ? ' ('.config('banjarcustom.express_fee').')' : '').'. Konfirmasi ke CS.'],
                ['title' => 'DP 50%', 'desc' => 'Bayar DP 50% untuk mulai produksi, 50% sisanya sebelum pesanan diambil atau dikirim.'],
            ] as $item)
                <div class="rounded-xl border border-border bg-card p-5">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/15 text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </span>
                    <h3 class="mt-3 font-semibold">{{ $item['title'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>

        <h2 class="mt-12 text-xl font-bold sm:text-2xl">Alur Pengerjaan</h2>
        <ol class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['step' => '1', 'title' => 'Konsultasi & Order', 'desc' => 'Isi form pesanan atau chat WhatsApp. Kami balas dengan estimasi harga dan waktu produksi.'],
                ['step' => '2', 'title' => 'Desain & Approval', 'desc' => 'Kirim desain Anda, atau minta tim kami buatkan. Kami kirim mockup sebelum produksi.'],
                ['step' => '3', 'title' => 'Produksi', 'desc' => 'Sablon dan jahit dikerjakan di workshop. Anda bisa minta update foto proses produksi.'],
                ['step' => '4', 'title' => 'QC & Pengiriman', 'desc' => 'Quality control satu per satu, lalu dikirim via kurir atau diambil sendiri di workshop.'],
            ] as $flow)
                <li class="rounded-xl border border-border bg-card p-5">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-primary text-sm font-bold text-primary-foreground">{{ $flow['step'] }}</span>
                    <h3 class="mt-3 font-semibold">{{ $flow['title'] }}</h3>
                    <p class="mt-1.5 text-sm leading-relaxed text-muted-foreground">{{ $flow['desc'] }}</p>
                </li>
            @endforeach
        </ol>

        <div class="mt-12 flex flex-col items-center gap-3 rounded-2xl bg-primary px-6 py-10 text-center text-primary-foreground sm:px-12">
            <h2 class="text-2xl font-bold sm:text-3xl">Siap Mulai Pesan?</h2>
            <p class="max-w-xl text-sm leading-relaxed text-primary-foreground/85 sm:text-base">
                Konsultasikan kebutuhan seragam, kaos tim, atau merchandise Anda — gratis tanpa minimum konsultasi.
            </p>
            <div class="mt-3 flex flex-wrap justify-center gap-3">
                <a href="{{ route('pesan.create') }}" class="inline-flex h-12 items-center rounded-md bg-primary-foreground px-8 text-sm font-bold text-primary transition-transform hover:-translate-y-0.5">
                    Isi Form Pesanan
                </a>
                <a href="{{ route('lokasi') }}" class="inline-flex h-12 items-center rounded-md border border-primary-foreground/40 px-8 text-sm font-semibold transition-colors hover:bg-primary-foreground/10">
                    Lokasi Workshop
                </a>
            </div>
        </div>
    </div>

</x-layouts.app>
