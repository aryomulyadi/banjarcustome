<x-layouts.app
    :title="'Katalog Jersey — Harga, Spesifikasi & Upgrade - Banjar Custome'"
    :description="'Katalog jersey custom Banjar Custome: harga dasar Rp85.000 per pcs, minimal 12 pcs, produksi 14-25 hari kerja, VIP ORDER, biaya desain, opsi kerah & bahan bergambar, dan pilihan produk jersey siap pesan.'"
>

    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span aria-hidden="true">/</span>
            <span class="text-foreground" aria-current="page">Katalog Jersey</span>
        </nav>

        <x-jsonld-breadcrumbs :items="[
            ['name' => 'Beranda', 'url' => route('home')],
            ['name' => 'Katalog Jersey', 'url' => route('katalog-jersey')],
        ]" />

        @php
            $j = config('banjarcustom.jersey', []);
            $rp = fn (int $n): string => 'Rp '.number_format($n, 0, ',', '.');
            $feeLabel = fn (int $fee): string => $fee === 0 ? 'Standar (Rp 0)' : '+'.number_format($fee, 0, ',', '.');
            $vnFee = (int) (((collect($jerseyCollars)->firstWhere('name', 'V-Neck') ?? [])['fee']) ?? 5000);
            $embosFee = (int) (((collect($jerseyFabrics)->firstWhere('name', 'Embos Tofo') ?? [])['fee']) ?? 15000);
        @endphp

        <span class="text-xs font-semibold uppercase tracking-widest text-primary">Katalog Jersey</span>
        <h1 class="mt-2 text-3xl font-bold sm:text-4xl">Katalog Jersey Custom</h1>
        <p class="mt-3 max-w-2xl text-sm leading-relaxed text-muted-foreground">
            Harga, spesifikasi dasar, aturan pemesanan, produk jersey, serta pilihan upgrade kerah dan bahan.
            Semua harga berlaku per pcs kecuali biaya desain.
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

        @if ($jerseyProducts->isNotEmpty())
            <div class="mt-12">
                <div class="flex items-end justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-bold sm:text-2xl">Produk Jersey</h2>
                        <p class="mt-1 text-sm text-muted-foreground">Pilih produk jersey kami, lalu kirim desain tim Anda.</p>
                    </div>
                </div>
                <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($jerseyProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
        <div class="mt-4 grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($jerseyCollars as $collar)
                <div class="overflow-hidden rounded-xl border border-border bg-card">
                    @if (! empty($collar['image']))
                        <x-img
                            :src="$collar['image']"
                            :alt="$collar['name']"
                            loading="lazy"
                            class="aspect-[4/3] w-full object-cover"
                        />
                    @else
                        <x-placeholder-image label="Kerah" class="aspect-[4/3]" />
                    @endif
                    <div class="flex items-center justify-between gap-2 p-3">
                        <p class="min-w-0 truncate text-sm font-semibold">{{ $collar['name'] }}</p>
                        <span class="shrink-0 rounded-full border border-primary/30 bg-primary/5 px-2 py-0.5 text-[11px] font-semibold text-primary">
                            {{ $feeLabel((int) $collar['fee']) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        <h2 class="mt-12 text-xl font-bold sm:text-2xl">Upgrade Bahan (Fabric)</h2>
        <p class="mt-2 text-sm text-muted-foreground">Bahan Dryfit Milano sudah termasuk harga dasar. Pilihan lain menambah biaya per pcs:</p>
        <div class="mt-4 grid gap-4 sm:grid-cols-3 lg:grid-cols-4">
            @foreach ($jerseyFabrics as $fabric)
                <div class="overflow-hidden rounded-xl border border-border bg-card">
                    @if (! empty($fabric['image']))
                        <x-img
                            :src="$fabric['image']"
                            :alt="$fabric['name']"
                            loading="lazy"
                            class="aspect-[4/3] w-full object-cover"
                        />
                    @else
                        <x-placeholder-image label="Bahan" class="aspect-[4/3]" />
                    @endif
                    <div class="flex items-center justify-between gap-2 p-3">
                        <p class="min-w-0 truncate text-sm font-semibold">{{ $fabric['name'] }}</p>
                        <span class="shrink-0 rounded-full border border-primary/30 bg-primary/5 px-2 py-0.5 text-[11px] font-semibold text-primary">
                            {{ $feeLabel((int) $fabric['fee']) }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>

        @php
            $exampleUnit = (int) ($j['base_price'] ?? 0) + $vnFee + $embosFee;
            $exampleQty = (int) ($j['min_order'] ?? 12);
            $hexFlags = JSON_HEX_APOS | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_QUOT;
            $firstCollar = $jerseyCollars[0] ?? ['name' => 'O-Neck', 'fee' => 0];
            $firstFabric = $jerseyFabrics[0] ?? ['name' => 'Dryfit Milano', 'fee' => 0];
            $rpFmt = fn (int $n): string => number_format($n, 0, ',', '.');
            $defaultUnit = (int) ($j['base_price'] ?? 0) + (int) $firstCollar['fee'] + (int) $firstFabric['fee'];
            $defaultWaText = "Halo Banjar Custome, saya mau pesan jersey dari Katalog Jersey:\n"
                .'- Kerah: '.$firstCollar['name'].((int) $firstCollar['fee'] > 0 ? ' (+Rp '.$rpFmt((int) $firstCollar['fee']).')' : ' (standar)')."\n"
                .'- Bahan: '.$firstFabric['name'].((int) $firstFabric['fee'] > 0 ? ' (+Rp '.$rpFmt((int) $firstFabric['fee']).')' : ' (standar)')."\n"
                .'- Jumlah: '.$exampleQty.' pcs'."\n"
                .'- VIP ORDER: tidak'."\n"
                .'- Biaya desain: tidak'."\n"
                .'Estimasi total: Rp '.$rpFmt($defaultUnit * $exampleQty).' (estimasi, harga final dikonfirmasi admin).';
        @endphp
        <div class="mt-12 rounded-2xl border border-border bg-card p-6">
            <h2 class="text-lg font-bold">Contoh Perhitungan</h2>
            <p class="mt-1 text-sm text-muted-foreground">
                12 pcs jersey kerah V-Neck ({{ $feeLabel($vnFee) }}) bahan Embos ({{ $feeLabel($embosFee) }}):
            </p>
            <p class="mt-3 text-sm leading-relaxed">
                ({{ $rp((int) ($j['base_price'] ?? 0)) }} + {{ number_format($vnFee, 0, ',', '.') }} + {{ number_format($embosFee, 0, ',', '.') }}) x {{ $exampleQty }} pcs
                = <span class="font-bold text-primary">{{ $rp($exampleUnit * $exampleQty) }}</span>
            </p>
            <p class="mt-2 text-xs leading-relaxed text-muted-foreground">
                Belum termasuk biaya desain {{ $rp((int) ($j['design_fee'] ?? 50000)) }} (sekali, revisi bebas) dan opsional
                VIP ORDER {{ $rp((int) ($j['vip_fee'] ?? 0)) }} per jersey. Harga final dikonfirmasi admin sebelum produksi.
            </p>
        </div>

        <div class="mt-12 rounded-2xl border border-primary/30 bg-primary/5 p-6" x-cloak
            x-data='{
                qty: {{ $exampleQty }},
                collar: {{ json_encode($firstCollar["name"], $hexFlags) }},
                fabric: {{ json_encode($firstFabric["name"], $hexFlags) }},
                vip: false,
                design: false,
                collars: {{ json_encode($jerseyCollars, $hexFlags) }},
                fabrics: {{ json_encode($jerseyFabrics, $hexFlags) }},
                base: {{ (int) ($j["base_price"] ?? 0) }},
                vipFee: {{ (int) ($j["vip_fee"] ?? 0) }},
                designFeeAmount: {{ (int) ($j["design_fee"] ?? 0) }},
                minOrder: {{ $exampleQty }},
                waBase: {{ json_encode(config("banjarcustom.whatsapp_link"), $hexFlags) }},
                fmt(n) { return Number(n || 0).toLocaleString("id-ID"); },
                rp(n) { return "Rp " + this.fmt(n); },
                get collarFee() { const c = this.collars.find(o => o.name === this.collar); return c ? (Number(c.fee) || 0) : 0; },
                get fabricFee() { const f = this.fabrics.find(o => o.name === this.fabric); return f ? (Number(f.fee) || 0) : 0; },
                get unitTotal() { return this.base + this.collarFee + this.fabricFee + (this.vip ? this.vipFee : 0); },
                get total() { const q = Math.max(0, Number(this.qty) || 0); return this.unitTotal * q + (this.design ? this.designFeeAmount : 0); },
                get qtyValid() { return (Number(this.qty) || 0) >= this.minOrder; },
                get waHref() {
                    const q = Math.max(0, Number(this.qty) || 0);
                    const lines = [
                        "Halo Banjar Custome, saya mau pesan jersey dari Katalog Jersey:",
                        "- Kerah: " + this.collar + (this.collarFee ? " (+Rp " + this.fmt(this.collarFee) + ")" : " (standar)"),
                        "- Bahan: " + this.fabric + (this.fabricFee ? " (+Rp " + this.fmt(this.fabricFee) + ")" : " (standar)"),
                        "- Jumlah: " + q + " pcs",
                        "- VIP ORDER: " + (this.vip ? "ya (+Rp " + this.fmt(this.vipFee) + "/pcs)" : "tidak"),
                        "- Biaya desain: " + (this.design ? "ya (Rp " + this.fmt(this.designFeeAmount) + ")" : "tidak"),
                        "Estimasi total: Rp " + this.fmt(this.total) + " (estimasi, harga final dikonfirmasi admin)."
                    ];
                    return this.waBase + "?text=" + encodeURIComponent(lines.join("\n"));
                }
            }'>

            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-lg font-bold">Kalkulator Estimasi Jersey</h2>
                    <p class="mt-1 text-xs text-muted-foreground">Pilih spesifikasi & jumlah — total terhitung langsung.</p>
                </div>
                <span class="rounded-full border border-primary/30 bg-card px-3 py-1 text-xs font-semibold text-primary">Estimasi</span>
            </div>

            <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div class="space-y-1.5">
                    <label for="est-qty" class="text-xs font-medium text-muted-foreground">Jumlah (pcs)</label>
                    <input
                        id="est-qty"
                        type="number"
                        min="{{ $exampleQty }}"
                        step="1"
                        x-model.number="qty"
                        class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                </div>
                <div class="space-y-1.5">
                    <label for="est-collar" class="text-xs font-medium text-muted-foreground">Kerah (upgrade)</label>
                    <select
                        id="est-collar"
                        x-model="collar"
                        class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                        @foreach ($jerseyCollars as $option)
                            <option value="{{ $option['name'] }}" @selected($loop->first)>
                                {{ $option['name'] }} {{ (int) $option['fee'] > 0 ? '(+'.number_format((int) $option['fee'], 0, ',', '.').')' : '(standar)' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="space-y-1.5">
                    <label for="est-fabric" class="text-xs font-medium text-muted-foreground">Bahan (upgrade)</label>
                    <select
                        id="est-fabric"
                        x-model="fabric"
                        class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                        @foreach ($jerseyFabrics as $option)
                            <option value="{{ $option['name'] }}" @selected($loop->first)>
                                {{ $option['name'] }} {{ (int) $option['fee'] > 0 ? '(+'.number_format((int) $option['fee'], 0, ',', '.').')' : '(standar)' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex flex-col justify-center gap-2">
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="checkbox" x-model="vip" class="h-4 w-4 rounded border-input">
                        <span>VIP ORDER <span class="text-muted-foreground">(+{{ $rp((int) ($j['vip_fee'] ?? 0)) }}/pcs)</span></span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-sm">
                        <input type="checkbox" x-model="design" class="h-4 w-4 rounded border-input">
                        <span>Biaya desain <span class="text-muted-foreground">({{ $rp((int) ($j['design_fee'] ?? 0)) }} sekali)</span></span>
                    </label>
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-4 rounded-xl border border-border bg-card p-4">
                <dl class="space-y-1 text-xs text-muted-foreground">
                    <div class="flex gap-2"><dt class="w-32 font-medium text-foreground">Harga dasar</dt><dd x-text="rp(base)"></dd></div>
                    <div class="flex gap-2"><dt class="w-32 font-medium text-foreground">Kerah</dt><dd x-text="collarFee ? '+'.rp(collarFee) : 'standar'"></dd></div>
                    <div class="flex gap-2"><dt class="w-32 font-medium text-foreground">Bahan</dt><dd x-text="fabricFee ? '+'.rp(fabricFee) : 'standar'"></dd></div>
                    <div class="flex gap-2"><dt class="w-32 font-medium text-foreground">VIP ORDER</dt><dd x-text="vip ? '+'.rp(vipFee)+'/pcs' : 'tidak'"></dd></div>
                    <div class="flex gap-2"><dt class="w-32 font-medium text-foreground">Biaya desain</dt><dd x-text="design ? rp(designFeeAmount) : 'tidak'"></dd></div>
                    <div class="flex gap-2 pt-1"><dt class="w-32 font-medium text-foreground">Per pcs</dt><dd class="font-semibold text-foreground" x-text="rp(unitTotal)"></dd></div>
                </dl>
                <div class="text-right">
                    <p class="text-xs font-semibold uppercase tracking-widest text-primary">Estimasi Total</p>
                    <p class="mt-0.5 text-3xl font-black text-primary sm:text-4xl" x-text="rp(total)"></p>
                    <p class="mt-1 text-[11px] text-muted-foreground" x-text="fmt(Math.max(0, Number(qty) || 0)) + ' pcs'"></p>
                </div>
            </div>

            <p class="mt-2 text-[11px] leading-relaxed text-muted-foreground">
                Estimasi otomatis — harga final dikonfirmasi admin sebelum produksi. Minimal pemesanan {{ $exampleQty }} pcs.
            </p>
            <p class="mt-1 text-xs font-medium text-destructive" x-show="!qtyValid" x-cloak>
                Jumlah minimal {{ $exampleQty }} pcs untuk pemesanan jersey.
            </p>

            <div class="mt-4 flex flex-wrap gap-3">
                <x-button
                    href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode($defaultWaText) }}"
                    x-bind:href="waHref"
                    target="_blank"
                    rel="noopener noreferrer"
                    size="lg"
                >
                    Pesan via WhatsApp
                </x-button>
                <x-button href="{{ route('pesan.create') }}" size="lg" variant="outline">
                    Isi Form Pesanan
                </x-button>
            </div>
        </div>

        @php
            $faqItems = [
                [
                    'Berapa minimal pemesanan jersey?',
                    'Minimal '.$exampleQty.' pcs untuk satu jenis jersey. Kombinasi kerah dan bahan yang berbeda dihitung terpisah. Gunakan Kalkulator Estimasi di atas untuk menghitung total kebutuhan Anda.',
                ],
                [
                    'Berapa lama proses produksi jersey?',
                    'Produksi jersey memakan waktu '.$j['lead_time'].' setelah desain disetujui dan DP dibayar. VIP ORDER ('.$rp((int) ($j['vip_fee'] ?? 0)).' per jersey) mempercepat pengerjaan.',
                ],
                [
                    'Bagaimana ketentuan pembayarannya?',
                    'DP minimal '.($j['dp_percent'] ?? 50).'% sebelum produksi dimulai dan wajib lunas sebelum pengiriman. DP hangus jika pesanan dibatalkan.',
                ],
                [
                    'Berapa biaya desain dan berapa kali revisi?',
                    'Biaya desain '.$rp((int) ($j['design_fee'] ?? 50000)).' sekali bayar untuk pembuatan desain awal, pengerjaan 1-2 hari kerja. Revisi bebas hingga desain fix.',
                ],
                [
                    'Apa perbedaan opsi kerah dan bahan?',
                    'Kerah O-Neck dan bahan Dryfit Milano sudah termasuk harga dasar. Opsi lainnya menambah biaya per pcs (lihat tabel Upgrade Kerah dan Upgrade Bahan di atas). Semua opsi bisa dicoba di Kalkulator Estimasi.',
                ],
                [
                    'Bagaimana cara memesan jersey?',
                    'Kirim desain tim Anda lewat Form Pesanan atau WhatsApp, sertakan jumlah, pilihan kerah, dan bahan. Admin menghitung harga final sebelum Anda membayar DP.',
                ],
            ];
        @endphp
        <div class="mt-12">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="text-xl font-bold sm:text-2xl">Tanya Jawab Jersey</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Pertanyaan yang sering diajukan sebelum pesan jersey.</p>
                </div>
                <a href="{{ route('faq') }}" class="text-sm font-medium text-primary transition-colors hover:underline">FAQ umum &rarr;</a>
            </div>
            <div class="mt-4 space-y-3">
                @foreach ($faqItems as [$q, $a])
                    <details class="group rounded-xl border border-border bg-card p-5 open:bg-muted/40">
                        <summary class="flex cursor-pointer list-none items-start justify-between gap-4 text-sm font-semibold sm:text-base">
                            <span>{{ $q }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-5 w-5 shrink-0 text-primary transition-transform group-open:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                        </summary>
                        <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>

        <div class="mt-12 flex flex-col items-center gap-3 rounded-2xl bg-primary px-6 py-10 text-center text-primary-foreground sm:px-12">
            <h2 class="text-2xl font-bold sm:text-3xl">Siap Pesan Jersey?</h2>
            <p class="max-w-xl text-sm leading-relaxed text-primary-foreground/85 sm:text-base">
                Minimal {{ $j['min_order'] ?? 12 }} pcs - kirim desain tim Anda, kami bantu cek kesiapan cetak.
            </p>
            <div class="mt-3 flex flex-wrap justify-center gap-3">
                <x-button href="{{ route('pesan.create') }}" size="xl" variant="invert">
                    Isi Form Pesanan
                </x-button>
                <x-button href="{{ config('banjarcustom.whatsapp_link') }}" size="xl" variant="invert-outline" target="_blank" rel="noopener noreferrer">
                    Konsultasi WhatsApp
                </x-button>
            </div>
        </div>
    </div>

    <script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">{!! json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => collect($faqItems)->map(fn (array $item) => [
            '@type' => 'Question',
            'name' => $item[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item[1]],
        ])->values(),
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>

    @if ($jerseyProducts->isNotEmpty())
        <script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">{!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'ItemList',
            'name' => 'Produk Jersey Banjar Custome',
            'itemListElement' => $jerseyProducts->values()->map(fn ($product, int $index) => array_filter([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $product->title,
                'url' => route('produk.show', $product->slug),
                'image' => $product->image ? asset('storage/'.$product->image) : null,
            ]))->values(),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif

</x-layouts.app>
