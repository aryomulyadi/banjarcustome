<x-layouts.app
    :title="'Banjar Custom — Jasa Konveksi & Sablon Custom Banjarmasin'"
    :description="'Jasa konveksi dan sablon custom di Banjarmasin: kaos, jersey printing, kemeja, jaket, seragam, dan merchandise. Custom desain, ecer & grosir, kirim ke seluruh Indonesia.'"
>

    {{-- Hero Slider --}}
    <section class="mx-auto max-w-[1440px] px-4 pt-4 sm:px-6 sm:pt-6 lg:px-8">
        <div
            class="relative h-[460px] overflow-hidden rounded-2xl border border-border bg-muted sm:h-[480px] lg:h-[520px]"
            x-data="heroSlider({{ count($slides) }})"
            @touchstart.passive="swipeStart($event)"
            @touchend="swipeEnd($event)"
        >
            @foreach ($slides as $i => $slide)
                <div
                    x-cloak
                    x-show="index === {{ $i }}"
                    x-transition.opacity.duration.700ms
                    class="absolute inset-0"
                >
                    @php
                        $slideImage = $slide['image'] ?? '';
                        $slideWebp = preg_replace('/\.(jpe?g)$/i', '.webp', $slideImage);
                        $slideSrc = preg_match('/\.(jpe?g)$/i', $slideImage) && file_exists(public_path($slideWebp))
                            ? $slideWebp
                            : $slideImage;
                        $slideSrcset = null;
                        if (str_ends_with($slideSrc, '.webp')) {
                            $slideVariant = preg_replace('/\.webp$/i', '-1024.webp', $slideSrc);
                            if ($slideVariant !== $slideSrc && file_exists(public_path($slideVariant))) {
                                $slideSrcset = asset($slideVariant).' 1024w, '.asset($slideSrc).' 1600w';
                            }
                        }
                    @endphp
                    @if (! empty($slide['image']) && file_exists(public_path($slide['image'])))
                        <img
                            src="{{ asset($slideSrc) }}"
                            @if ($slideSrcset) srcset="{{ $slideSrcset }}" sizes="(min-width: 1280px) 1216px, calc(100vw - 32px)" @endif
                            alt="{{ $slide['title'] }}"
                            width="1600"
                            height="700"
                            decoding="async"
                            @if ($i === 0) fetchpriority="high" @else loading="lazy" @endif
                            class="absolute inset-0 h-full w-full object-cover object-right sm:object-center"
                        >
                    @else
                        <x-placeholder-image label="Banner Promo {{ $i + 1 }}" class="absolute inset-0 h-full w-full" />
                    @endif
                    <div class="absolute inset-0 bg-gradient-to-t from-background via-background/85 to-background/30 sm:bg-gradient-to-r sm:from-background/95 sm:via-background/75 sm:to-transparent"></div>

                    <div class="relative flex h-full max-w-2xl flex-col justify-end gap-3 px-5 pb-14 sm:justify-center sm:gap-4 sm:px-12 sm:pb-0">
                        <h1 class="text-2xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                            {{ $slide['title'] }}
                        </h1>
                        <p class="max-w-xl text-xs leading-relaxed text-muted-foreground sm:text-base">
                            {{ $slide['subtitle'] }}
                        </p>
                        <div class="mt-2 flex flex-col gap-2.5 sm:flex-row sm:gap-3">
                            <x-button href="{{ route('produk.index') }}" size="lg" class="w-full sm:w-auto">
                                Lihat Katalog
                            </x-button>
                            <x-button href="{{ route('pesan.create') }}" size="lg" variant="outline" class="w-full sm:w-auto">
                                Mulai Pesan
                            </x-button>
                        </div>
                    </div>
                </div>
            @endforeach

            @if (count($slides) > 1)
                <button type="button" @click="prev()" class="hidden sm:flex absolute left-3 top-1/2 h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-background/80 text-foreground shadow-sm transition hover:bg-background" aria-label="Slide sebelumnya">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button type="button" @click="next()" class="hidden sm:flex absolute right-3 top-1/2 h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-background/80 text-foreground shadow-sm transition hover:bg-background" aria-label="Slide berikutnya">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </button>

                <button
                    type="button"
                    @click="toggle()"
                    class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-background/80 text-foreground shadow-sm transition hover:bg-background sm:h-10 sm:w-10"
                    :aria-label="paused ? 'Putar slide otomatis' : 'Jeda slide otomatis'"
                    :aria-pressed="paused.toString()"
                >
                    <svg x-show="paused" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                    <svg x-show="!paused" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><rect width="6" height="14" x="4" y="5" rx="1"/><rect width="6" height="14" x="14" y="5" rx="1"/></svg>
                </button>

                <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
                    @foreach ($slides as $i => $slide)
                        <button
                            type="button"
                            @click="index = {{ $i }}"
                            class="relative flex h-6 w-6 items-center justify-center after:absolute after:-inset-2 after:content-['']"
                            aria-label="Slide {{ $i + 1 }}"
                        >
                            <span
                                class="block h-2.5 rounded-full transition-all"
                                :class="index === {{ $i }} ? 'w-6 bg-primary' : 'w-2.5 bg-muted-foreground/40 hover:bg-muted-foreground/70'"
                            ></span>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Dipercaya oleh — marquee logo klien --}}
    <x-logo-marquee :items="$clients" />

    {{-- Alur Pemesanan --}}
    <section class="mx-auto max-w-[1440px] px-4 py-10 sm:px-6 lg:px-8">
        <div class="mb-6 text-center">
            <h2 class="text-2xl font-bold sm:text-3xl">Alur Pemesanan</h2>
            <p class="mt-1 text-sm text-muted-foreground">Empat langkah mudah dari konsultasi sampai pesanan di tangan Anda.</p>
        </div>

        <ol class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['no' => '01', 'title' => 'Konsultasi & Pilih Produk', 'desc' => 'Ceritakan kebutuhan Anda lewat WhatsApp — kami bantu pilih model, bahan, dan jumlah yang paling pas.'],
                ['no' => '02', 'title' => 'Desain & Approval', 'desc' => 'Kirim desain sendiri atau minta tim kami buatkan. Desain disetujui dulu sebelum produksi dimulai.'],
                ['no' => '03', 'title' => 'DP & Mulai Produksi', 'desc' => 'Bayar DP, pesanan langsung masuk antrean produksi, dan statusnya bisa dilacak kapan saja.'],
                ['no' => '04', 'title' => 'QC & Pengiriman', 'desc' => 'Dicek rapi sebelum dikirim ke seluruh Indonesia — atau ambil langsung di tempat kami.'],
            ] as $step)
                <li class="rounded-xl border border-border bg-card p-5">
                    <span class="text-3xl font-black tracking-tight text-primary/30" aria-hidden="true">{{ $step['no'] }}</span>
                    <h3 class="mt-2 font-semibold">{{ $step['title'] }}</h3>
                    <p class="mt-1 text-sm text-muted-foreground">{{ $step['desc'] }}</p>
                </li>
            @endforeach
        </ol>
    </section>

    {{-- Katalog Preview --}}
    <section class="mx-auto max-w-[1440px] px-4 pb-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold sm:text-3xl">Katalog Produk</h2>
                <p class="mt-1 text-sm text-muted-foreground">Pilihan produk siap custom sesuai kebutuhan tim, kantor, dan komunitas.</p>
            </div>
            <a href="{{ route('produk.index') }}" class="shrink-0 inline-block py-1.5 text-sm font-semibold text-primary hover:underline">
                Lihat Semua →
            </a>
        </div>

        <div x-data="productCarousel">
            <x-scroll-row grid="sm:grid-cols-2 lg:grid-cols-4" data-carousel-track>
                @forelse ($products as $product)
                    <x-scroll-row-item data-slide>
                    <x-product-card :product="$product" />
                    </x-scroll-row-item>
                @empty
                    <p class="col-span-full w-full shrink-0 py-8 text-center text-sm text-muted-foreground">Belum ada produk.</p>
                @endforelse
            </x-scroll-row>

            @if (count($products) > 1)
                <div class="mt-4 flex items-center justify-center gap-1.5 lg:hidden">
                    @foreach ($products as $i => $product)
                        <button
                            type="button"
                            @click="goTo({{ $i }})"
                            :class="active === {{ $i }} ? 'w-6 bg-primary' : 'w-1.5 bg-border'"
                            class="h-1.5 rounded-full transition-all duration-300"
                            aria-label="Slide produk {{ $i + 1 }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- CTA Custom Design --}}
    <section class="mx-auto max-w-[1440px] px-4 py-8 sm:px-6 sm:py-10 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl bg-primary px-5 py-10 text-center text-primary-foreground sm:px-12 sm:py-16">
            <div class="mx-auto max-w-3xl">
                <span class="inline-block rounded-full bg-primary-foreground/20 px-3 py-1 text-xs font-semibold uppercase tracking-widest">
                    Custom Design
                </span>
                <h2 class="mt-4 text-2xl font-bold leading-tight sm:text-4xl">
                    Buat Seragam/Kaos Custom Anda Sendiri!
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-xs leading-relaxed text-primary-foreground/85 sm:text-base">
                    Kirim desain, tentukan warna dan jumlahnya — kami produksi dengan sablon awet dan rapi.
                    Mulai dari satuan hingga grosir.
                </p>
                <div class="mt-6 flex flex-col items-center justify-center gap-3 sm:flex-row">
                    <x-button href="{{ route('pesan.create') }}" size="xl" variant="invert" class="w-full sm:w-auto">
                        Mulai Pesan Sekarang
                    </x-button>
                    <x-button
                        href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custom, saya mau konsultasi custom seragam/kaos.') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        size="xl"
                        variant="invert-outline"
                        class="w-full sm:w-auto"
                    >
                        Chat WhatsApp
                    </x-button>
                </div>
            </div>
        </div>
    </section>

    {{-- Galeri Preview --}}
    <section class="mx-auto max-w-[1440px] px-4 pb-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold sm:text-3xl">Galeri Hasil Produksi</h2>
                <p class="mt-1 text-sm text-muted-foreground">Sebagian hasil kerja kami untuk komunitas, sekolah, dan perusahaan.</p>
            </div>
            <a href="{{ route('galeri') }}" class="shrink-0 inline-block py-1.5 text-sm font-semibold text-primary hover:underline">
                Lihat Semua →
            </a>
        </div>

        <x-scroll-row grid="sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($galleries as $gallery)
                <x-scroll-row-item>
                <figure class="group overflow-hidden rounded-xl border border-border bg-card">
                    @if ($gallery->image)
                        <x-img :src="$gallery->image" :alt="$gallery->title" width="800" height="600" loading="lazy" decoding="async" sizes="(min-width: 1024px) 400px, (min-width: 640px) calc(50vw - 32px), calc(100vw - 32px)" class="aspect-[4/3] w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                    @else
                        <x-placeholder-image label="Galeri" class="transition-transform duration-300 group-hover:scale-105" />
                    @endif
                    <figcaption class="p-4">
                        <p class="text-sm font-semibold">{{ $gallery->title }}</p>
                        @if ($gallery->caption)
                            <p class="mt-1 line-clamp-2 text-xs text-muted-foreground">{{ $gallery->caption }}</p>
                        @endif
                    </figcaption>
                </figure>
                </x-scroll-row-item>
            @empty
                <p class="col-span-full w-full shrink-0 py-8 text-center text-sm text-muted-foreground">Belum ada galeri.</p>
            @endforelse
        </x-scroll-row>
    </section>

    {{-- Profil Perusahaan --}}
    <section class="mx-auto max-w-[1440px] px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid items-center gap-8 rounded-2xl border border-border bg-muted/40 p-6 sm:p-10 lg:grid-cols-2">
            <div>
                <span class="text-xs font-semibold uppercase tracking-widest text-primary">Tentang Kami</span>
                <h2 class="mt-2 text-2xl font-bold sm:text-3xl">Banjar Custom — Konveksi &amp; Sablon Banjarmasin</h2>
                <p class="mt-4 text-sm leading-relaxed text-muted-foreground sm:text-base">
                    Kami melayani pembuatan pakaian custom seperti kaos, jersey printing, kemeja, seragam, jaket,
                    dan merchandise untuk satuan, grup, maupun komunitas. Dengan sablon berkualitas dan proses
                    produksi yang terkontrol, kami siap membantu mewujudkan desain tim Anda.
                </p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <a href="{{ route('tentang') }}" class="inline-flex h-10 items-center rounded-md bg-primary px-5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90">
                        Selengkapnya
                    </a>
                    <a href="{{ route('size-chart') }}" class="inline-flex h-10 items-center rounded-md border border-border bg-background px-5 text-sm font-semibold transition-colors hover:bg-secondary">
                        Panduan Ukuran
                    </a>
                </div>
            </div>

            <div class="grid gap-3 sm:gap-4">
                @foreach ($stats as $stat)
                    <div class="rounded-xl border border-border bg-card p-5 text-center sm:p-6">
                        <p class="text-4xl font-bold text-primary sm:text-5xl">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-xs font-medium uppercase tracking-wider text-muted-foreground">{{ $stat['label'] }}</p>
                    </div>
                @endforeach

                <div class="rounded-xl border border-border bg-card p-4 sm:p-5">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Cabang Kami</p>
                    <ul class="mt-3 space-y-2.5">
                        @foreach ([
                            ['city' => 'Banjarmasin', 'prov' => 'Kalimantan Selatan'],
                            ['city' => 'Banjarbaru', 'prov' => 'Kalimantan Selatan'],
                            ['city' => 'Bandung', 'prov' => 'Jawa Barat'],
                        ] as $branch)
                            <li class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-primary">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                </span>
                                <span class="text-sm font-medium">{{ $branch['city'] }}</span>
                                <span class="text-xs text-muted-foreground">— {{ $branch['prov'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </section>

</x-layouts.app>
