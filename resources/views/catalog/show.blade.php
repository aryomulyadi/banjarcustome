@php
    $gallery = $product->images->values();
    $ogImagePath = $product->image ?: $gallery->first()?->image;
@endphp
<x-layouts.app
    :title="$product->title . ' — Banjar Custome'"
    :description="Str::limit(strip_tags($product->description ?: 'Pesan ' . $product->title . ' custom di Banjar Custome, Banjarmasin. Sablon awet, produksi rapi, ecer & grosir.'), 160)"
    :ogImage="$ogImagePath ? asset('storage/' . $ogImagePath) : asset('images/banner-1.jpg')"
    :ogImageWidth="800"
    :ogImageHeight="600"
    :ogType="'product'"
>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex flex-wrap items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span>/</span>
            <a href="{{ route('produk.index') }}" class="hover:text-primary">Katalog</a>
            <span>/</span>
            @if ($product->category)
                <a href="{{ route('kategori', $product->category->slug) }}" class="hover:text-primary">{{ $product->category->name }}</a>
                <span>/</span>
            @endif
            <span class="text-foreground">{{ $product->title }}</span>
        </nav>

        <x-jsonld-breadcrumbs :items="array_values(array_filter([
            ['name' => 'Beranda', 'url' => route('home')],
            ['name' => 'Katalog', 'url' => route('produk.index')],
            $product->category ? ['name' => $product->category->name, 'url' => route('kategori', $product->category->slug)] : null,
            ['name' => $product->title, 'url' => route('produk.show', $product->slug)],
        ]))" />

        <div class="grid gap-8 lg:grid-cols-2">
            {{-- Gambar --}}
            <div class="overflow-hidden rounded-2xl border border-border bg-card" @if ($gallery->count() > 1) x-data="{ index: 0 }" @endif>
                @if ($gallery->isEmpty())
                    @if ($product->image)
                        <x-img :src="$product->image" :alt="$product->title" width="800" height="600" fetchpriority="high" decoding="async" sizes="(min-width: 1024px) 600px, calc(100vw - 32px)" class="aspect-[4/3] w-full object-cover" />
                    @else
                        <x-placeholder-image label="Foto Produk" class="aspect-[4/3]" />
                    @endif
                @else
                    <div class="relative">
                        @foreach ($gallery as $i => $galleryImage)
                            <div @if ($i > 0) style="display: none;" @endif x-show="index === {{ $i }}">
                                <x-img
                                    :src="$galleryImage->image"
                                    :alt="$galleryImage->alt ?: $product->title"
                                    width="800"
                                    height="600"
                                    fetchpriority="{{ $i === 0 ? 'high' : 'auto' }}"
                                    decoding="async"
                                    sizes="(min-width: 1024px) 600px, calc(100vw - 32px)"
                                    class="aspect-[4/3] w-full object-cover"
                                />
                            </div>
                        @endforeach

                        @if ($gallery->count() > 1)
                            <button
                                type="button"
                                x-on:click="index = (index - 1 + {{ $gallery->count() }}) % {{ $gallery->count() }}"
                                class="absolute left-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-background/80 text-foreground shadow transition-colors hover:bg-background"
                                aria-label="Foto sebelumnya"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                            </button>
                            <button
                                type="button"
                                x-on:click="index = (index + 1) % {{ $gallery->count() }}"
                                class="absolute right-2 top-1/2 flex h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full bg-background/80 text-foreground shadow transition-colors hover:bg-background"
                                aria-label="Foto berikutnya"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                            </button>
                            <div class="absolute inset-x-0 bottom-2 flex justify-center gap-1">
                                @foreach ($gallery as $i => $galleryImage)
                                    <button
                                        type="button"
                                        x-on:click="index = {{ $i }}"
                                        class="flex h-6 w-6 items-center justify-center"
                                        aria-label="Foto ke-{{ $i + 1 }}"
                                    >
                                        <span
                                            class="block h-2 w-2 rounded-full transition-colors"
                                            x-bind:class="index === {{ $i }} ? 'bg-primary' : 'bg-background/60'"
                                        ></span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($gallery->count() > 1)
                        <div class="flex gap-2 border-t border-border p-2">
                            @foreach ($gallery as $i => $galleryImage)
                                <button type="button" x-on:click="index = {{ $i }}" class="shrink-0 overflow-hidden rounded-md border-2 transition-colors" x-bind:class="index === {{ $i }} ? 'border-primary' : 'border-transparent opacity-70 hover:opacity-100'">
                                    <x-img :src="$galleryImage->image" :alt="$galleryImage->alt ?: $product->title.' foto '.($i + 1)" loading="lazy" sizes="56px" class="h-14 w-14 object-cover" />
                                </button>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>

            {{-- Info --}}
            <div>
                @if ($product->category)
                    <a href="{{ route('kategori', $product->category->slug) }}" class="inline-block rounded-full bg-secondary px-3 py-1 text-xs font-semibold uppercase tracking-wider text-secondary-foreground transition-colors hover:text-primary">
                        {{ $product->category->name }}
                    </a>
                @endif

                <h1 class="mt-3 text-3xl font-bold leading-tight">{{ $product->title }}</h1>

                <p class="mt-2 text-xl font-semibold text-primary">
                    {{ $product->price_estimate ?? 'Harga hubungi CS' }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">Harga estimasi — final menyesuaikan bahan, jumlah, dan tingkat kerumitan desain.</p>

                @if (\App\Support\ServiceTypes::isJerseyTitle($product->title) || \App\Support\ServiceTypes::isJerseyTitle($product->category?->name))
                    <a href="{{ route('katalog-jersey') }}" class="mt-4 flex items-center justify-between gap-3 rounded-xl border border-primary/30 bg-primary/5 px-4 py-3 text-sm font-semibold transition-colors hover:bg-primary/10">
                        <span>Lihat Katalog Jersey — harga dasar, minimal order, upgrade kerah &amp; bahan</span>
                        <span aria-hidden="true">→</span>
                    </a>
                @endif

                @if ($product->description)
                    <p class="mt-4 leading-relaxed text-muted-foreground">{!! nl2br(e($product->description)) !!}</p>
                @endif

                @if ($product->colors->isNotEmpty())
                    <div class="mt-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wider">Warna Tersedia</h2>
                        <p class="mt-1 text-xs text-muted-foreground">Klik warna untuk melihat semua produk yang tersedia dalam warna tersebut.</p>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach ($product->colors as $color)
                                <a
                                    href="{{ route('produk.index', ['warna' => $color->name]) }}"
                                    class="inline-flex items-center gap-2 rounded-full border border-border px-3 py-1.5 text-sm transition-colors hover:border-primary hover:text-primary focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                    aria-label="Filter katalog, warna {{ $color->name }}"
                                >
                                    <span class="h-4 w-4 rounded-full border border-border" style="background-color: {{ $color->hex }}"></span>
                                    {{ $color->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button href="{{ route('pesan.create', ['produk' => $product->slug]) }}" size="xl">
                        Pesan Produk Ini
                    </x-button>
                    <x-button
                        href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya mau tanya produk: '.$product->title) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        size="xl"
                        variant="outline"
                    >
                        Tanya via WhatsApp
                    </x-button>
                </div>

                <div class="mt-6 rounded-xl border border-border bg-muted/50 p-4 text-sm text-muted-foreground">
                    <p class="font-medium text-foreground">Butuh bantuan memilih ukuran?</p>
                    <p class="mt-1">Lihat <a href="{{ route('size-chart') }}" class="font-medium text-primary hover:underline">Panduan Ukuran</a> untuk memastikan pas.</p>
                </div>
            </div>
        </div>

        @if ($related->isNotEmpty())
            <section class="mt-14">
                <h2 class="text-xl font-bold sm:text-2xl">Produk Lain di {{ $product->category?->name ?? 'Kategori Ini' }}</h2>
                <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    <script type="application/ld+json" nonce="{{ request()->attributes->get('csp_nonce') }}">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => Str::limit(strip_tags($product->description ?? ''), 200),
            'image' => ($product->image || $gallery->isNotEmpty())
                ? collect([$product->image])
                    ->concat($gallery->pluck('image'))
                    ->filter()
                    ->map(fn (string $path) => asset('storage/'.$path))
                    ->values()
                    ->all()
                : asset('images/banner-1.jpg'),
            'url' => route('produk.show', $product->slug),
            'brand' => ['@type' => 'Brand', 'name' => 'Banjar Custome'],
            'offers' => [
                '@type' => 'Offer',
                'url' => route('produk.show', $product->slug),
                'priceCurrency' => 'IDR',
                'availability' => 'https://schema.org/InStock',
            ],
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>

</x-layouts.app>
