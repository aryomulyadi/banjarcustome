@php
    $activeCategory = request()->routeIs('kategori') ? request()->route('slug') : null;
@endphp

<header
    class="sticky top-0 z-50 w-full border-b border-border bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/80"
    x-data="{ mobileOpen: false }"
>
    <div class="mx-auto flex h-16 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
        <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-2.5">
            <x-logo />
            <span class="leading-tight">
                <span class="block text-base font-bold">
                    Banjar <span class="text-primary">Custome</span>
                </span>
                <span class="block text-[10px] font-medium uppercase tracking-widest text-muted-foreground">
                    Konveksi &amp; Sablon
                </span>
            </span>
        </a>

        <nav class="hidden flex-1 items-center gap-0.5 lg:flex">
            @foreach ($navCategories as $category)
                <a
                    href="{{ route('kategori', $category->slug) }}"
                    class="rounded-md px-3 py-2 text-sm font-medium transition-colors {{ $activeCategory === $category->slug ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
                >
                    {{ $category->name }}
                </a>
            @endforeach
        </nav>
        <div class="hidden flex-1 lg:block"></div>

        <div class="flex items-center gap-2">
            <button
                type="button"
                x-data="theme"
                @click="toggle()"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
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
                href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya ingin konsultasi desain/') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md bg-primary text-primary-foreground transition-colors hover:bg-primary/90"
                aria-label="Chat WhatsApp"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
            </a>

            <button
                type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border lg:hidden"
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

    <div id="menu-mobile" class="border-t border-border lg:hidden" x-show="mobileOpen" x-cloak>
        <nav class="mx-auto grid max-w-7xl gap-1 px-4 py-3 sm:px-6">
            @foreach ($navCategories as $category)
                <a
                    href="{{ route('kategori', $category->slug) }}"
                    class="rounded-md px-3 py-2 text-sm font-medium {{ $activeCategory === $category->slug ? 'bg-secondary text-primary' : 'text-muted-foreground hover:bg-secondary hover:text-foreground' }}"
                >
                    {{ $category->name }}
                </a>
            @endforeach
            @auth
                @if (auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-secondary">
                        Dashboard Admin
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm font-medium text-muted-foreground hover:bg-secondary">
                            Keluar
                        </button>
                    </form>
                @endif
            @endauth
            <a
                href="{{ config('banjarcustom.whatsapp_link') }}?text={{ rawurlencode('Halo Banjar Custome, saya ingin bertanya soal pesanan custom.') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="rounded-md bg-primary px-3 py-2 text-center text-sm font-medium text-primary-foreground"
            >
                Hubungi CS
            </a>
        </nav>
    </div>
</header>
