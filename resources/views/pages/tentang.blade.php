<x-layouts.app>

    <div class="mx-auto max-w-4xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span>/</span>
            <span class="text-foreground">Tentang Kami</span>
        </nav>

        <span class="text-xs font-semibold uppercase tracking-widest text-primary">Tentang Kami</span>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Banjar Custome — Jasa Konveksi &amp; Sablon Banjarmasin</h1>

        <div class="mt-6 space-y-4 leading-relaxed text-muted-foreground">
            <p>
                Banjar Custome adalah penyedia jasa konveksi dan sablon yang berlokasi di Banjarmasin,
                Kalimantan Selatan. Kami melayani pembuatan pakaian custom seperti kaos, jersey printing,
                kemeja, seragam, jaket, hingga merchandise untuk satuan, grup, komunitas, sekolah, maupun perusahaan.
            </p>
            <p>
                Dengan tim produksi yang berpengalaman dan teknik sablon berkualitas, hasil kami tetap rapi,
                elastis, dan tidak mudah pecah meskipun dicuci berulang kali. Kami siap membantu dari tahap
                konsultasi desain hingga pengiriman.
            </p>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2">
            <div class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-semibold">Visi</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                    Menjadi konveksi kepercayaan masyarakat Banjarmasin dan Kalimantan Selatan untuk kebutuhan
                    pakaian custom yang berkualitas dan tepat waktu.
                </p>
            </div>
            <div class="rounded-xl border border-border bg-card p-5">
                <h2 class="font-semibold">Misi</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                    Menghadirkan sablon awet, jahitan rapi, harga transparan, dan pelayanan responsif —
                    baik untuk satu pcs maupun pesanan ratusan.
                </p>
            </div>
        </div>

        <h2 class="mt-12 text-xl font-bold sm:text-2xl">Alur Pemesanan</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @php
                $steps = [
                    ['step' => '01', 'title' => 'Konsultasi', 'desc' => 'Hubungi CS, tentukan produk, jumlah, dan warna.'],
                    ['step' => '02', 'title' => 'Desain', 'desc' => 'Kirim desain Anda atau minta bantuan tim desain kami.'],
                    ['step' => '03', 'title' => 'Produksi', 'desc' => 'Pesanan diproduksi dan dikontrol kualitasnya.'],
                    ['step' => '04', 'title' => 'Terima', 'desc' => 'Barang dikirim atau diambil di lokasi kami.'],
                ];
            @endphp
            @foreach ($steps as $item)
                <div class="rounded-xl border border-border bg-card p-5">
                    <span class="text-2xl font-bold text-primary">{{ $item['step'] }}</span>
                    <h3 class="mt-2 font-semibold">{{ $item['title'] }}</h3>
                    <p class="mt-1 text-sm text-muted-foreground">{{ $item['desc'] }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-12 rounded-2xl bg-primary p-6 text-primary-foreground sm:p-8">
            <h2 class="text-xl font-bold sm:text-2xl">Siap Pesan?</h2>
            <p class="mt-2 text-sm text-primary-foreground/85">
                Konsultasikan kebutuhan Anda sekarang — gratis, tanpa minimal pesanan untuk konsultasi.
            </p>
            <div class="mt-5 flex flex-wrap gap-3">
                <a href="{{ route('pesan.create') }}" class="inline-flex h-11 items-center rounded-md bg-primary-foreground px-6 text-sm font-bold text-primary transition-transform hover:-translate-y-0.5">
                    Buat Pesanan
                </a>
                <a href="{{ config('banjarcustom.whatsapp_link') }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-11 items-center rounded-md border border-primary-foreground/40 px-6 text-sm font-semibold transition-colors hover:bg-primary-foreground/10">
                    Chat WhatsApp
                </a>
            </div>
        </div>
    </div>

</x-layouts.app>
