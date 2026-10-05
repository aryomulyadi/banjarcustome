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
                        <legend class="text-sm font-semibold uppercase tracking-wider">Warna</legend>
                        <div class="mt-3 space-y-2">
                            <label class="flex cursor-pointer items-center gap-2.5">
                                <input type="radio" name="warna" value="" class="peer sr-only" @checked(request('warna') === null || request('warna') === '')>
                                <span class="h-5 w-5 rounded-full border border-border bg-muted peer-checked:ring-2 peer-checked:ring-ring peer-checked:ring-offset-2 peer-checked:ring-offset-background"></span>
                                <span class="text-sm text-muted-foreground">Semua warna</span>
                            </label>

                            @foreach ($colors as $color)
                                <label class="flex cursor-pointer items-center gap-2.5">
                                    <input type="radio" name="warna" value="{{ $color->name }}" class="peer sr-only" @checked(request('warna') === $color->name)>
                                    <span class="h-5 w-5 rounded-full border border-border peer-checked:ring-2 peer-checked:ring-ring peer-checked:ring-offset-2 peer-checked:ring-offset-background" style="background-color: {{ $color->hex }}"></span>
                                    <span class="text-sm text-muted-foreground">{{ $color->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="mt-6 flex gap-2">
                        <button type="submit" class="inline-flex h-9 flex-1 items-center justify-center rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90">
                            Terapkan
                        </button>
                        <a href="{{ route('produk.index') }}" class="inline-flex h-9 items-center justify-center rounded-md border border-border px-4 text-sm font-medium transition-colors hover:bg-secondary">
                            Reset
                        </a>
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
                            <span class="rounded-full bg-secondary px-3 py-1">Warna: {{ request('warna') }}</span>
                        @endif
                    </div>
                @endif

                @if ($products->isEmpty())
                    <div class="rounded-xl border border-dashed border-border p-12 text-center">
                        <p class="font-medium">Produk tidak ditemukan</p>
                        <p class="mt-1 text-sm text-muted-foreground">Coba ubah kata kunci atau hapus filter warna.</p>
                        <a href="{{ route('produk.index') }}" class="mt-4 inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground">
                            Lihat Semua Produk
                        </a>
                    </div>
                @else
                    <p class="mb-4 text-sm text-muted-foreground">{{ $products->total() }} produk ditemukan</p>
                    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" />
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
