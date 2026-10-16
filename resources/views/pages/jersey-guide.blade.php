<x-layouts.app
    :title="'Panduan Jersey Custom 2026-2027 - Harga, Spesifikasi &amp; Upgrade - Banjar Custome'"
    :description="'Pedoman lengkap jersey custom Banjar Custome 2026-2027: harga dasar Rp85.000 per pcs, minimal 12 pcs, produksi 14-25 hari kerja, VIP ORDER, biaya desain, dan tabel upgrade kerah serta bahan.'"
>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span aria-hidden="true">/</span>
            <span class="text-foreground" aria-current="page">Panduan Jersey</span>
        </nav>

        @php
            $j = config('banjarcustom.jersey', []);
            $rp = fn (int $n): string => 'Rp '.number_format($n, 0, ',', '.');
            $collars = $j['collars'] ?? [];
            $fabrics = $j['fabrics'] ?? [];
        @endphp

        <span class="text-xs font-semibold uppercase tracking-widest text-primary">Katalog Jersey 2026-2027</span>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Panduan Jersey Custom</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-muted-foreground">
            Harga, spesifikasi dasar, aturan pemesanan, dan biaya upgrade kerah serta bahan untuk jersey reguler
            (atasan only) periode 2026-2027. Semua harga berlaku per pcs kecuali biaya desain.
        </p>

        <div class="mt-8 rounded-2xl border border-primary/30 bg-primary/5 p-6 sm:p-8">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-primary">Harga Dasar Jersey Reguler</p>
                    <p class="mt-1 text-4xl font-black sm:text-5xl">{{ $rp((int) ($j['base_price'] ?? 0)) }} <span class="text-base font-semibold text-muted-foreground">/ pcs</span></p>
                    <p class="mt-2 text-xs text-muted-foreground">Atasan only - belum termasuk upgrade kerah/bahan dan biaya desain.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($j['base_spec'] ?? [] as $spec)
                        <span class="rounded-full border border-primary/30 bg-card px-3 py-1.5 text-xs font-semibold">{{ $spec }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-border bg-card p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-primary">Minimal Order</p>
                <p class="mt-2 text-2xl font-black">{{ $j['min_order'] ?? 12 }} pcs</p>
                <p class="mt-1 text-xs leading-relaxed text-muted-foreground">Minimal pemesanan untuk satu jenis jersey.</p>
            </div>
            <div class="rounded-xl border border-border bg-card p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-primary">Waktu Produksi</p>
                <p class="mt-2 text-2xl font-black">{{ $j['lead_time'] ?? '14-25 hari kerja' }}</p>
                <p class="mt-1 text-xs leading-relaxed text-muted-foreground">Opsi VIP ORDER {{ $rp((int) ($j['vip_fee'] ?? 0)) }} per jersey untuk pengerjaan lebih cepat.</p>
            </div>
            <div class="rounded-xl border border-border bg-card p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-primary">Pembayaran</p>
                <p class="mt-2 text-2xl font-black">DP {{ $j['dp_percent'] ?? 50 }}%</p>
                <p class="mt-1 text-xs leading-relaxed text-muted-foreground">DP minimal 50% sebelum produksi, wajib lunas sebelum pengiriman. DP hangus jika batal.</p>
            </div>
            <div class="rounded-xl border border-border bg-card p-5">
                <p class="text-xs font-semibold uppercase tracking-widest text-primary">Biaya Desain</p>
                <p class="mt-2 text-2xl font-black">{{ $rp((int) ($j['design_fee'] ?? 50000)) }}</p>
                <p class="mt-1 text-xs leading-relaxed text-muted-foreground">Pengerjaan 1-2 hari kerja, revisi bebas hingga fix.</p>
            </div>
        </div>

        <h2 class="mt-12 text-xl font-bold sm:text-2xl">Upgrade Kerah (Collar)</h2>
        <p class="mt-2 text-sm text-muted-foreground">Kerah O-Neck sudah termasuk harga dasar. Pilihan lain menambah biaya per pcs:</p>
        <div class="mt-4 overflow-x-auto rounded-xl border border-border">
            <table class="w-full min-w-[360px] text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left text-xs uppercase tracking-wider text-muted-foreground">
                        <th class="px-4 py-3 font-semibold">Model Kerah</th>
                        <th class="px-4 py-3 text-right font-semibold">Tambahan per pcs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($collars as $collar)
                        <tr>
                            <td class="px-4 py-2.5 {{ (int) $collar['fee'] === 0 ? 'font-semibold' : '' }}">{{ $collar['name'] }}</td>
                            <td class="px-4 py-2.5 text-right {{ (int) $collar['fee'] === 0 ? 'font-semibold' : 'font-medium text-primary' }}">
                                {{ (int) $collar['fee'] === 0 ? 'Standar (Rp 0)' : '+'.number_format((int) $collar['fee'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <h2 class="mt-12 text-xl font-bold sm:text-2xl">Upgrade Bahan (Fabric)</h2>
        <p class="mt-2 text-sm text-muted-foreground">Bahan Dryfit Milano sudah termasuk harga dasar. Pilihan lain menambah biaya per pcs:</p>
        <div class="mt-4 overflow-x-auto rounded-xl border border-border">
            <table class="w-full min-w-[360px] text-sm">
                <thead>
                    <tr class="border-b border-border bg-muted/50 text-left text-xs uppercase tracking-wider text-muted-foreground">
                        <th class="px-4 py-3 font-semibold">Bahan</th>
                        <th class="px-4 py-3 text-right font-semibold">Tambahan per pcs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    @foreach ($fabrics as $fabric)
                        <tr>
                            <td class="px-4 py-2.5 {{ (int) $fabric['fee'] === 0 ? 'font-semibold' : '' }}">{{ $fabric['name'] }}</td>
                            <td class="px-4 py-2.5 text-right {{ (int) $fabric['fee'] === 0 ? 'font-semibold' : 'font-medium text-primary' }}">
                                {{ (int) $fabric['fee'] === 0 ? 'Standar (Rp 0)' : '+'.number_format((int) $fabric['fee'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @php
            $exampleUnit = (int) ($j['base_price'] ?? 0) + 5000 + 15000;
            $exampleQty = (int) ($j['min_order'] ?? 12);
        @endphp
        <div class="mt-12 rounded-2xl border border-border bg-card p-6">
            <h2 class="text-lg font-bold">Contoh Perhitungan</h2>
            <p class="mt-1 text-sm text-muted-foreground">12 pcs jersey kerah V-Neck (+Rp5.000) bahan Embos (+Rp15.000):</p>
            <p class="mt-3 text-sm leading-relaxed">
                ({{ $rp((int) ($j['base_price'] ?? 0)) }} + Rp5.000 + Rp15.000) x {{ $exampleQty }} pcs
                = <span class="font-bold text-primary">{{ $rp($exampleUnit * $exampleQty) }}</span>
            </p>
            <p class="mt-2 text-xs leading-relaxed text-muted-foreground">
                Belum termasuk biaya desain {{ $rp((int) ($j['design_fee'] ?? 50000)) }} (sekali, revisi bebas) dan opsional
                VIP ORDER {{ $rp((int) ($j['vip_fee'] ?? 0)) }} per jersey. Harga final dikonfirmasi admin sebelum produksi.
            </p>
        </div>

        <div class="mt-12 flex flex-col items-center gap-3 rounded-2xl bg-primary px-6 py-10 text-center text-primary-foreground sm:px-12">
            <h2 class="text-2xl font-bold sm:text-3xl">Siap Pesan Jersey?</h2>
            <p class="max-w-xl text-sm leading-relaxed text-primary-foreground/85 sm:text-base">
                Minimal {{ $j['min_order'] ?? 12 }} pcs - kirim desain tim Anda, kami bantu cek kesiapan cetak.
            </p>
            <div class="mt-3 flex flex-wrap justify-center gap-3">
                <a href="{{ route('pesan.create') }}" class="inline-flex h-12 items-center rounded-md bg-primary-foreground px-8 text-sm font-bold text-primary transition-transform hover:-translate-y-0.5">
                    Isi Form Pesanan
                </a>
                <a href="{{ config('banjarcustom.whatsapp_link') }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-12 items-center rounded-md border border-primary-foreground/40 px-8 text-sm font-semibold transition-colors hover:bg-primary-foreground/10">
                    Konsultasi WhatsApp
                </a>
            </div>
        </div>
    </div>

</x-layouts.app>
