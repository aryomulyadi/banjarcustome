<x-layouts.app
    :title="$product->title . ' — Banjar Custome'"
    :description="Str::limit(strip_tags($product->description ?: 'Pesan ' . $product->title . ' custom di Banjar Custome, Banjarmasin. Sablon awet, produksi rapi, ecer & grosir.'), 160)"
    :ogImage="$product->image ? asset('storage/' . $product->image) : asset('images/banner-1.jpg')"
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

        <div class="grid gap-8 lg:grid-cols-2">
            {{-- Gambar --}}
            <div class="overflow-hidden rounded-2xl border border-border bg-card">
                @if ($product->image)
                    <img src="{{ asset('storage/'.$product->image) }}" alt="{{ $product->title }}" width="800" height="600" decoding="async" class="aspect-[4/3] w-full object-cover">
                @else
                    <x-placeholder-image label="Foto Produk" class="aspect-[4/3]" />
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

                @if ($product->description)
                    <p class="mt-4 leading-relaxed text-muted-foreground">{{ $product->description }}</p>
                @endif

                @if ($product->colors->isNotEmpty())
                    <div class="mt-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wider">Warna Tersedia</h2>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach ($product->colors as $color)
                                <span class="inline-flex items-center gap-2 rounded-full border border-border px-3 py-1.5 text-sm">
                                    <span class="h-4 w-4 rounded-full border border-border" style="background-color: {{ $color->hex }}"></span>
                                    {{ $color->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('pesan.create', ['produk' => $product->slug]) }}" class="inline-flex h-12 items-center rounded-md bg-primary px-7 text-sm font-bold text-primary-foreground transition-colors hover:bg-primary/90">
                        Pesan Produk Ini
                    </a>
                    <a
                        href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya mau tanya produk: '.$product->title) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-12 items-center rounded-md border border-border px-7 text-sm font-semibold transition-colors hover:bg-secondary"
                    >
                        Tanya via WhatsApp
                    </a>
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

    <script type="application/ld+json">
        {!! json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $product->title,
            'description' => Str::limit(strip_tags($product->description ?? ''), 200),
            'image' => $product->image ? asset('storage/'.$product->image) : asset('images/banner-1.jpg'),
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
