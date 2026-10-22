<x-layouts.app
    :title="($activeCategory ? $activeCategory->name . ' — ' : 'Katalog Produk — ') . 'Banjar Custome'"
    :description="$activeCategory?->description ?: 'Katalog produk konveksi & sablon Banjar Custome: kaos, jersey, kemeja, jaket, seragam, dan merchandise. Tersedia ecer & grosir.'"
>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span>/</span>
            @if ($activeCategory)
                <a href="{{ route('produk.index') }}" class="hover:text-primary">Katalog</a>
                <span>/</span>
                <span class="text-foreground">{{ $activeCategory->name }}</span>
            @else
                <span class="text-foreground">Katalog</span>
            @endif
        </nav>

        <x-jsonld-breadcrumbs :items="array_values(array_filter([
            ['name' => 'Beranda', 'url' => route('home')],
            $activeCategory ? ['name' => 'Katalog', 'url' => route('produk.index')] : null,
            $activeCategory ? ['name' => $activeCategory->name, 'url' => route('kategori', $activeCategory->slug)] : ['name' => 'Katalog Produk', 'url' => route('produk.index')],
        ]))" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold sm:text-3xl">{{ $activeCategory?->name ?? 'Katalog Produk' }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ $activeCategory?->description ?? 'Semua produk konveksi & sablon Banjar Custome. Gunakan filter untuk mempersempit pilihan.' }}
            </p>
        </div>

        <div class="grid gap-8 lg:grid-cols-[260px_1fr]">
            {{-- Sidebar Filter --}}
            <aside class="h-fit rounded-xl border border-border bg-card p-5 lg:sticky lg:top-24">
                <form method="GET" action="{{ request()->url() }}">
                    @if ($activeCategory)
                        <input type="hidden" name="kategori" value="{{ $activeCategory->slug }}">
                    @endif

                    <div>
                        <h2 class="text-sm font-semibold uppercase tracking-wider">Pencarian</h2>
                        <div class="mt-3 flex gap-2">
                            <input
                                type="text"
                                name="q"
                                value="{{ request('q') }}"
                                placeholder="Cari produk..."
                                aria-label="Cari produk"
                                class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            >
                        </div>
                    </div>

                    <fieldset class="mt-6">
                        <legend class="text-sm font-semibold uppercase tracking-wider">Warna Tersedia</legend>
                        <p class="mt-1 text-xs text-muted-foreground">Produk yang tersedia dalam warna pilihan.</p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <label class="cursor-pointer">
                                <input type="radio" name="warna" value="" class="peer sr-only" @checked(request('warna') === null || request('warna') === '')>
                                <span class="inline-flex items-center rounded-full border border-border px-3 py-1.5 text-xs text-muted-foreground transition-colors peer-checked:border-primary peer-checked:text-foreground peer-focus-visible:ring-2 peer-focus-visible:ring-ring peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-background">
                                    Semua
                                </span>
                            </label>

                            @foreach ($colors as $color)
                                <label class="cursor-pointer">
                                    <input
                                        type="radio"
                                        name="warna"
                                        value="{{ $color->name }}"
                                        class="peer sr-only"
                                        aria-label="{{ $color->name }}, {{ $color->products_count }} produk"
                                        @checked(request('warna') === $color->name)
                                    >
                                    <span
                                        class="block h-7 w-7 rounded-full border border-border transition-shadow peer-checked:ring-2 peer-checked:ring-ring peer-checked:ring-offset-2 peer-checked:ring-offset-background peer-focus-visible:ring-2 peer-focus-visible:ring-ring peer-focus-visible:ring-offset-2 peer-focus-visible:ring-offset-background"
                                        style="background-color: {{ $color->hex }}"
                                        title="{{ $color->name }} — {{ $color->products_count }} produk"
                                    ></span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="mt-6 flex gap-2">
                        <x-button type="submit" size="sm" class="flex-1">
                            Terapkan
                        </x-button>
                        <x-button href="{{ request()->url() }}" size="sm" variant="outline">
                            Reset
                        </x-button>
                    </div>
                </form>

                <div class="mt-6 border-t border-border pt-5">
                    <h2 class="text-sm font-semibold uppercase tracking-wider">Kategori</h2>
                    <div class="mt-3 flex flex-wrap gap-2 lg:flex-col">
                        @foreach ($categories as $category)
                            <a
                                href="{{ route('kategori', $category->slug) }}"
                                class="flex items-center justify-between gap-2 rounded-md px-3 py-2 text-sm transition-colors {{ $activeCategory?->id === $category->id ? 'bg-secondary font-medium text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
                            >
                                <span>{{ $category->name }}</span>
                                <span class="text-xs text-muted-foreground/70">{{ $category->products_count }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </aside>

            {{-- Grid Produk --}}
            <div>
                @if (request()->filled('q') || request()->filled('warna'))
                    <div class="mb-4 flex flex-wrap items-center gap-2 text-sm">
                        <span class="text-muted-foreground">Filter aktif:</span>
                        @if (request('q'))
                            <span class="rounded-full bg-secondary px-3 py-1">Pencarian: "{{ request('q') }}"</span>
                        @endif
                        @if (request('warna'))
                            <span class="inline-flex items-center gap-2 rounded-full bg-secondary px-3 py-1">
                                @php $activeColorHex = $colors->firstWhere('name', request('warna'))?->hex; @endphp
                                @if ($activeColorHex)
                                    <span class="h-3 w-3 rounded-full border border-border" style="background-color: {{ $activeColorHex }}"></span>
                                @endif
                                Warna: {{ request('warna') }}
                            </span>
                        @endif
                    </div>
                @endif

                @php $isJerseyCategory = $activeCategory && \Illuminate\Support\Str::contains(strtolower($activeCategory->name), 'jersey'); @endphp

                @if ($isJerseyCategory)
                    <a
                        href="{{ route('katalog-jersey') }}"
                        class="mb-6 flex items-center justify-between gap-3 rounded-xl border border-primary/30 bg-primary/5 px-5 py-4 text-sm font-semibold transition-colors hover:bg-primary/10"
                    >
                        <span>
                            Jersey custom — harga dasar Rp 85.000/pcs, minimal 12 pcs, upgrade kerah &amp; bahan ada di Katalog Jersey.
                        </span>
                        <span aria-hidden="true">→</span>
                    </a>
                @endif

                @if ($products->isEmpty() && ! $isJerseyCategory)
                    <div class="rounded-xl border border-dashed border-border p-12 text-center">
                        <p class="font-medium">Produk tidak ditemukan</p>
                        <p class="mt-1 text-sm text-muted-foreground">Coba ubah kata kunci atau hapus filter warna.</p>
                        <x-button href="{{ route('produk.index') }}" size="sm" class="mt-4">
                            Lihat Semua Produk
                        </x-button>
                    </div>
                @elseif ($products->isEmpty())
                    {{-- Kategori jersey: banner di atas sudah menjadi ajakan utama. --}}
                @else
                    <p class="mb-4 text-sm text-muted-foreground">{{ $products->total() }} produk ditemukan</p>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" :highlight-color="request()->filled('warna') ? request('warna') : null" />
                        @endforeach
                    </div>

                    <div class="mt-8">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-layouts.app>
