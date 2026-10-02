<x-layouts.app>

    {{-- Hero Slider --}}
    <section class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
        <div class="relative h-[440px] overflow-hidden rounded-2xl border border-border bg-muted sm:h-[480px]" x-data="heroSlider({{ count($slides) }})">
            @foreach ($slides as $i => $slide)
                <div
                    x-cloak
                    class="absolute inset-0 transition-opacity duration-700"
                    :class="index === {{ $i }} ? 'opacity-100' : 'pointer-events-none opacity-0'"
                >
                    <x-placeholder-image label="Banner Promo {{ $i + 1 }}" class="absolute inset-0 h-full w-full" />
                    <div class="absolute inset-0 bg-gradient-to-r from-background/95 via-background/75 to-transparent"></div>

                    <div class="relative flex h-full max-w-2xl flex-col justify-center gap-4 px-6 sm:px-12">
                        <span class="w-fit rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold uppercase tracking-widest text-primary">
                            Banjarmasin · Kalimantan Selatan
                        </span>
                        <h1 class="text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                            {{ $slide['title'] }}
                        </h1>
                        <p class="max-w-xl text-sm leading-relaxed text-muted-foreground sm:text-base">
                            {{ $slide['subtitle'] }}
                        </p>
                        <div class="mt-2 flex flex-wrap gap-3">
                            <a href="{{ route('produk.index') }}" class="inline-flex h-11 items-center rounded-md bg-primary px-6 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90">
                                Lihat Katalog
                            </a>
                            <a href="{{ route('pesan.create') }}" class="inline-flex h-11 items-center rounded-md border border-border bg-background/80 px-6 text-sm font-semibold text-foreground transition-colors hover:bg-secondary">
                                Mulai Pesan
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach

            @if (count($slides) > 1)
                <button type="button" @click="prev()" class="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-background/80 text-foreground shadow-sm transition hover:bg-background" aria-label="Slide sebelumnya">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button type="button" @click="next()" class="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-background/80 text-foreground shadow-sm transition hover:bg-background" aria-label="Slide berikutnya">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                </button>

                <div class="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2">
                    @foreach ($slides as $i => $slide)
                        <button
                            type="button"
                            @click="index = {{ $i }}"
                            class="h-2.5 rounded-full transition-all"
                            :class="index === {{ $i }} ? 'w-6 bg-primary' : 'w-2.5 bg-muted-foreground/40 hover:bg-muted-foreground/70'"
                            aria-label="Slide {{ $i + 1 }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- USP --}}
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-3">
            <div class="flex items-start gap-4 rounded-xl border border-border bg-card p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary/15 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20h9"/><path d="M16.376 3.622a1 1 0 0 1 3.002 3.002L7.368 18.635a2 2 0 0 1-.855.506l-2.872.838a.5.5 0 0 1-.62-.62l.838-2.872a2 2 0 0 1 .506-.854z"/></svg>
                </span>
                <div>
                    <h3 class="font-semibold">Bebas Custom Desain</h3>
                    <p class="mt-1 text-sm text-muted-foreground">Punya desain sendiri? Kirim aja. Belum punya? Tim kami bantu buatkan.</p>
                </div>
            </div>

            <div class="flex items-start gap-4 rounded-xl border border-border bg-card p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary/15 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                </span>
                <div>
                    <h3 class="font-semibold">Sablon Awet &amp; Tidak Pecah</h3>
                    <p class="mt-1 text-sm text-muted-foreground">Bahan dan teknik sablon berkualitas, hasil tetap elastis setelah berulang kali dicuci.</p>
                </div>
            </div>

            <div class="flex items-start gap-4 rounded-xl border border-border bg-card p-5">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-primary/15 text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>
                </span>
                <div>
                    <h3 class="font-semibold">Melayani Ecer &amp; Grosir</h3>
                    <p class="mt-1 text-sm text-muted-foreground">Satuan untuk keperluan pribadi maupun ratusan pcs untuk komunitas dan perusahaan.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Katalog Preview --}}
    <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold sm:text-3xl">Katalog Produk</h2>
                <p class="mt-1 text-sm text-muted-foreground">Pilihan produk siap custom sesuai kebutuhan tim, kantor, dan komunitas.</p>
            </div>
            <a href="{{ route('produk.index') }}" class="shrink-0 text-sm font-semibold text-primary hover:underline">
                Lihat Semua →
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            @forelse ($products as $product)
                <x-product-card :product="$product" />
            @empty
                <p class="col-span-full py-8 text-center text-sm text-muted-foreground">Belum ada produk.</p>
            @endforelse
        </div>
    </section>

    {{-- CTA Custom Design --}}
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="relative overflow-hidden rounded-2xl bg-primary px-6 py-12 text-center text-primary-foreground sm:px-12 sm:py-16">
            <div class="mx-auto max-w-3xl">
                <span class="inline-block rounded-full bg-primary-foreground/20 px-3 py-1 text-xs font-semibold uppercase tracking-widest">
                    Custom Design
                </span>
                <h2 class="mt-4 text-3xl font-bold leading-tight sm:text-4xl">
                    Buat Seragam/Kaos Custom Anda Sendiri!
                </h2>
                <p class="mx-auto mt-3 max-w-2xl text-sm leading-relaxed text-primary-foreground/85 sm:text-base">
                    Kirim desain, tentukan warna dan jumlahnya — kami produksi dengan sablon awet dan rapi.
                    Mulai dari satuan hingga grosir.
                </p>
                <div class="mt-7 flex flex-wrap items-center justify-center gap-3">
                    <a href="{{ route('pesan.create') }}" class="inline-flex h-12 items-center rounded-md bg-primary-foreground px-8 text-sm font-bold text-primary transition-transform hover:-translate-y-0.5">
                        Mulai Pesan Sekarang
                    </a>
                    <a
                        href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya mau konsultasi custom seragam/kaos.') }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-12 items-center rounded-md border border-primary-foreground/40 px-8 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary-foreground/10"
                    >
                        Chat WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Galeri Preview --}}
    <section class="mx-auto max-w-7xl px-4 pb-4 sm:px-6 lg:px-8">
        <div class="mb-6 flex items-end justify-between gap-4">
            <div>
                <h2 class="text-2xl font-bold sm:text-3xl">Galeri Hasil Produksi</h2>
                <p class="mt-1 text-sm text-muted-foreground">Sebagian hasil kerja kami untuk komunitas, sekolah, dan perusahaan.</p>
            </div>
            <a href="{{ route('galeri') }}" class="shrink-0 text-sm font-semibold text-primary hover:underline">
                Lihat Semua →
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($galleries as $gallery)
                <figure class="group overflow-hidden rounded-xl border border-border bg-card">
                    @if ($gallery->image)
                        <img src="{{ asset('storage/'.$gallery->image) }}" alt="{{ $gallery->title }}" class="aspect-[4/3] w-full object-cover transition-transform duration-300 group-hover:scale-105">
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
            @empty
                <p class="col-span-full py-8 text-center text-sm text-muted-foreground">Belum ada galeri.</p>
            @endforelse
        </div>
    </section>

    {{-- Profil Perusahaan --}}
    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="grid items-center gap-8 rounded-2xl border border-border bg-muted/40 p-6 sm:p-10 lg:grid-cols-2">
            <div>
                <span class="text-xs font-semibold uppercase tracking-widest text-primary">Tentang Kami</span>
                <h2 class="mt-2 text-2xl font-bold sm:text-3xl">Banjar Custome — Konveksi &amp; Sablon Banjarmasin</h2>
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

            <div class="grid gap-4 sm:grid-cols-3">
                @foreach ($stats as $stat)
                    <div class="rounded-xl border border-border bg-card p-5 text-center">
                        <p class="text-2xl font-bold text-primary">{{ $stat['value'] }}</p>
                        <p class="mt-1 text-xs font-medium uppercase tracking-wider text-muted-foreground">{{ $stat['label'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

</x-layouts.app>
