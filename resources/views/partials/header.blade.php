@php
    $activeCategory = request()->routeIs('kategori') ? request()->route('slug') : null;
@endphp

<header
    class="sticky top-0 z-50 w-full border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80"
    x-data="{ mobileOpen: false }"
    x-effect="document.body.style.overflow = mobileOpen ? 'hidden' : ''"
>
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-1.5 px-3 sm:gap-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2 sm:gap-2.5">
            <x-logo />
            <span class="min-w-0 leading-tight">
                <span class="block truncate text-sm font-bold sm:text-base">
                    Banjar <span class="text-primary">Custome</span>
                </span>
                <span class="block truncate text-[9px] font-medium uppercase tracking-wider text-muted-foreground sm:text-[10px] sm:tracking-widest">
                    Konveksi &amp; Sablon
                </span>
            </span>
        </a>

        <nav class="hidden flex-1 items-center gap-1 lg:flex" aria-label="Navigasi utama">
            <a
                href="{{ route('home') }}"
                class="rounded-md px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('home') ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
            >
                Beranda
            </a>

            <div
                class="relative"
                x-data="{ kategoriOpen: false }"
                @click.outside="kategoriOpen = false"
                @keydown.escape.window="kategoriOpen = false"
                @focusout="if (!$el.contains($event.relatedTarget)) kategoriOpen = false"
            >
                <button
                    type="button"
                    id="tombol-kategori"
                    @click="kategoriOpen = !kategoriOpen"
                    @keydown.arrow-down.prevent="kategoriOpen = true; $nextTick(() => $refs.menuKategori.querySelector('a').focus())"
                    aria-haspopup="true"
                    aria-expanded="false"
                    :aria-expanded="kategoriOpen.toString()"
                    aria-controls="menu-kategori"
                    class="inline-flex items-center gap-1 rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $activeCategory !== null ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
                >
                    Kategori
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-3.5 w-3.5 transition-transform"
                        :class="kategoriOpen && 'rotate-180'"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    ><path d="m6 9 6 6 6-6" /></svg>
                </button>

                <div
                    id="menu-kategori"
                    x-ref="menuKategori"
                    x-cloak
                    x-show="kategoriOpen"
                    x-transition.origin.top.left
                    class="absolute left-0 top-full z-50 mt-2 w-60 rounded-xl border border-border bg-card p-2 shadow-lg"
                >
                    <p class="px-2.5 pb-1.5 pt-1 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                        Kategori Produk
                    </p>
                    <ul class="space-y-0.5">
                        @foreach ($navCategories as $category)
                            <li>
                                <a
                                    href="{{ route('kategori', $category->slug) }}"
                                    class="flex items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors {{ $activeCategory === $category->slug ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
                                >
                                    <span>{{ $category->name }}</span>
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-3.5 w-3.5 shrink-0 opacity-60"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    ><path d="m9 18 6-6-6-6" /></svg>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                    <div class="my-1.5 border-t border-border"></div>
                    <a
                        href="{{ route('produk.index') }}"
                        class="flex items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-sm font-medium transition-colors {{ request()->routeIs('produk.index') ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
                    >
                        <span>Semua Produk</span>
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-3.5 w-3.5 shrink-0 opacity-60"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            aria-hidden="true"
                        ><path d="M5 12h14" /><path d="m12 5 7 7-7 7" /></svg>
                    </a>
                </div>
            </div>

            <a
                href="{{ route('galeri') }}"
                class="rounded-md px-3 py-2 text-sm font-medium transition-colors {{ request()->routeIs('galeri') ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
            >
                Galeri
            </a>

            <span class="mx-1.5 h-6 w-px bg-border" aria-hidden="true"></span>

            <a
                href="{{ route('katalog-jersey') }}"
                class="inline-flex shrink-0 items-center gap-1.5 rounded-full px-3.5 py-2 text-sm font-semibold text-primary-foreground transition-colors bg-primary {{ request()->routeIs('katalog-jersey') ? 'ring-2 ring-primary/40' : 'hover:bg-primary/90' }}"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                ><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z" /></svg>
                Katalog Jersey
            </a>
        </nav>
        <div class="hidden flex-1 lg:block"></div>

        <div class="ml-auto flex items-center gap-1 sm:gap-2">
            <button
                type="button"
                x-data="theme"
                @click="toggle()"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground sm:h-10 sm:w-10"
                :aria-label="dark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap'"
            >
                <svg x-show="!dark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2"/><path d="M12 20v2"/><path d="m4.93 4.93 1.41 1.41"/><path d="m17.66 17.66 1.41 1.41"/><path d="M2 12h2"/><path d="M20 12h2"/><path d="m6.34 17.66-1.41 1.41"/><path d="m19.07 4.93-1.41 1.41"/></svg>
                <svg x-show="dark" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/></svg>
            </button>

            @auth
                @if (auth()->user()->isAdmin())
                    <a
                        href="{{ route('admin.dashboard') }}"
                        class="hidden items-center gap-2 rounded-md border border-border px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-secondary md:inline-flex"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex h-9 items-center gap-2 rounded-md border border-border px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
                        >
                            Keluar
                        </button>
                    </form>
                @elseif (auth()->user()->isProduksi())
                    <a
                        href="{{ route('admin.produksi') }}"
                        class="hidden items-center gap-2 rounded-md border border-border px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-secondary md:inline-flex"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                        Antrian Produksi
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="hidden md:block">
                        @csrf
                        <button
                            type="submit"
                            class="inline-flex h-9 items-center gap-2 rounded-md border border-border px-3 py-2 text-sm font-medium text-foreground transition-colors hover:bg-secondary"
                        >
                            Keluar
                        </button>
                    </form>
                @endif
            @endauth

            <a
                href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya ingin bertanya soal pesanan custom.') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="hidden items-center gap-2 rounded-md border border-border bg-secondary px-3 py-2 text-sm font-medium text-secondary-foreground transition-colors hover:bg-secondary/70 sm:inline-flex"
            >
                Hubungi CS
            </a>

            <a
                href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya ingin konsultasi desain.') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-primary text-primary-foreground transition-colors hover:bg-primary/90 sm:h-10 sm:w-10"
                aria-label="Chat WhatsApp"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
            </a>

            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border lg:hidden sm:h-10 sm:w-10"
                @click="mobileOpen = !mobileOpen"
                aria-label="Buka menu"
                aria-expanded="false"
                :aria-expanded="mobileOpen.toString()"
                aria-controls="menu-mobile"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
            </button>
        </div>
    </div>

    {{-- Backdrop overlay untuk mobile menu --}}
    <div
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-xs lg:hidden"
        x-show="mobileOpen"
        x-cloak
        @click="mobileOpen = false"
        x-transition.opacity
    ></div>

    <div
        id="menu-mobile"
        class="relative z-50 max-h-[calc(100dvh-4rem)] overflow-y-auto overscroll-contain border-t border-border bg-background shadow-xl lg:hidden"
        x-show="mobileOpen"
        x-cloak
        @click.outside="mobileOpen = false"
        @keydown.escape.window="mobileOpen = false"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
    >
        <nav class="mx-auto grid max-w-7xl gap-1 px-4 py-3 sm:px-6">
            <a
                href="{{ route('home') }}"
                class="rounded-md px-3 py-2.5 text-sm font-medium {{ request()->routeIs('home') ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
            >
                Beranda
            </a>

            <a
                href="{{ route('galeri') }}"
                class="rounded-md px-3 py-2.5 text-sm font-medium {{ request()->routeIs('galeri') ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
            >
                Galeri Hasil Produksi
            </a>

            <a
                href="{{ route('track.create') }}"
                class="rounded-md px-3 py-2.5 text-sm font-medium {{ request()->routeIs('track.*') ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
            >
                Cek Status Pesanan
            </a>

            <p class="px-3 pb-1 pt-2 text-[11px] font-semibold uppercase tracking-wider text-muted-foreground">
                Kategori
            </p>
            @foreach ($navCategories as $category)
                <a
                    href="{{ route('kategori', $category->slug) }}"
                    class="rounded-md px-3 py-2 text-sm font-medium {{ $activeCategory === $category->slug ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
                >
                    {{ $category->name }}
                </a>
            @endforeach
            <a
                href="{{ route('produk.index') }}"
                class="rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('produk.index') ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
            >
                Semua Produk
            </a>

            <div class="my-2 border-t border-border"></div>

            <a
                href="{{ route('katalog-jersey') }}"
                class="inline-flex items-center justify-center gap-2 rounded-md bg-primary px-3 py-2.5 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90 {{ request()->routeIs('katalog-jersey') ? 'ring-2 ring-primary/40' : '' }}"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                ><path d="M20.38 3.46 16 2a4 4 0 0 1-8 0L3.62 3.46a2 2 0 0 0-1.34 2.23l.58 3.47a1 1 0 0 0 .99.84H6v10c0 1.1.9 2 2 2h8a2 2 0 0 0 2-2V10h2.15a1 1 0 0 0 .99-.84l.58-3.47a2 2 0 0 0-1.34-2.23z" /></svg>
                Katalog Jersey
            </a>
            <p class="px-3 pb-1 text-center text-[11px] text-muted-foreground">
                Harga, MOQ &amp; panduan order
            </p>

            <div class="my-1 border-t border-border"></div>

            <div class="grid grid-cols-2 gap-1 px-1 py-1 text-xs">
                <a href="{{ route('size-chart') }}" class="rounded px-2 py-1.5 text-muted-foreground hover:bg-secondary hover:text-foreground">Panduan Ukuran</a>
                <a href="{{ route('layanan') }}" class="rounded px-2 py-1.5 text-muted-foreground hover:bg-secondary hover:text-foreground">Layanan</a>
                <a href="{{ route('tentang') }}" class="rounded px-2 py-1.5 text-muted-foreground hover:bg-secondary hover:text-foreground">Tentang Kami</a>
                <a href="{{ route('faq') }}" class="rounded px-2 py-1.5 text-muted-foreground hover:bg-secondary hover:text-foreground">Tanya Jawab (FAQ)</a>
                <a href="{{ route('lokasi') }}" class="col-span-2 rounded px-2 py-1.5 text-muted-foreground hover:bg-secondary hover:text-foreground">Lokasi Workshop</a>
            </div>

            @auth
                @if (auth()->user()->isAdmin())
                    <div class="my-1 border-t border-border"></div>
                    <a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-foreground hover:bg-secondary">
                        Dashboard Admin
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm font-medium text-destructive hover:bg-destructive/10">
                            Keluar
                        </button>
                    </form>
                @elseif (auth()->user()->isProduksi())
                    <div class="my-1 border-t border-border"></div>
                    <a href="{{ route('admin.produksi') }}" class="rounded-md px-3 py-2 text-sm font-medium text-foreground hover:bg-secondary">
                        Antrian Produksi
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm font-medium text-destructive hover:bg-destructive/10">
                            Keluar
                        </button>
                    </form>
                @endif
            @endauth

            <div class="mt-2 pt-2">
                <a
                    href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya ingin bertanya soal pesanan custom.') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="block rounded-md border border-border bg-secondary px-3 py-2.5 text-center text-sm font-medium text-secondary-foreground hover:bg-secondary/80"
                >
                    Hubungi CS via WhatsApp
                </a>
            </div>
        </nav>
    </div>
</header>
