@php
    $footerCategories = \App\Models\Category::select('name', 'slug')->orderBy('name')->get();
@endphp

<footer class="border-t border-border bg-muted/40">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-4 lg:px-8">
        <div class="md:col-span-1">
            <div class="flex items-center gap-2.5">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary text-sm font-bold text-primary-foreground">BC</span>
                <span class="text-base font-bold">Banjar <span class="text-primary">Custome</span></span>
            </div>
            <p class="mt-3 text-sm leading-relaxed text-muted-foreground">
                Jasa konveksi dan sablon custom di Banjarmasin. Kaos, jersey, kemeja, seragam, jaket, hingga merchandise —
                ecer maupun grosir.
            </p>
            <div class="mt-4 flex gap-2">
                <a
                    href="https://instagram.com/banjarcustom_konveksi.id"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
                    aria-label="Instagram Banjar Custome"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                </a>
                <a
                    href="{{ config('banjarcustom.whatsapp_link') }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-border text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground"
                    aria-label="WhatsApp Banjar Custome"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                </a>
            </div>
        </div>

        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider">Kategori</h3>
            <ul class="mt-4 space-y-2">
                @foreach ($footerCategories as $category)
                    <li>
                        <a href="{{ route('kategori', $category->slug) }}" class="text-sm text-muted-foreground transition-colors hover:text-primary">
                            {{ $category->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider">Informasi</h3>
            <ul class="mt-4 space-y-2">
                <li><a href="{{ route('tentang') }}" class="text-sm text-muted-foreground transition-colors hover:text-primary">Tentang Kami</a></li>
                <li><a href="{{ route('galeri') }}" class="text-sm text-muted-foreground transition-colors hover:text-primary">Galeri</a></li>
                <li><a href="{{ route('size-chart') }}" class="text-sm text-muted-foreground transition-colors hover:text-primary">Panduan Ukuran</a></li>
                <li><a href="{{ route('kebijakan-privasi') }}" class="text-sm text-muted-foreground transition-colors hover:text-primary">Kebijakan Privasi</a></li>
                <li><a href="{{ route('syarat-ketentuan') }}" class="text-sm text-muted-foreground transition-colors hover:text-primary">Syarat &amp; Ketentuan Pemesanan</a></li>
            </ul>
        </div>

        <div>
            <h3 class="text-sm font-semibold uppercase tracking-wider">Kontak</h3>
            <ul class="mt-4 space-y-3 text-sm text-muted-foreground">
                <li class="flex gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                    <span>{{ config('banjarcustom.address') }}</span>
                </li>
                <li class="flex gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    <a href="{{ config('banjarcustom.whatsapp_link') }}" target="_blank" rel="noopener noreferrer" class="transition-colors hover:text-primary">
                        {{ config('banjarcustom.whatsapp') }}
                    </a>
                </li>
                <li class="flex gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="mt-0.5 h-4 w-4 shrink-0 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                    <a href="https://instagram.com/banjarcustom_konveksi.id" target="_blank" rel="noopener noreferrer" class="transition-colors hover:text-primary">
                        @banjarcustom_konveksi.id
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <div class="border-t border-border py-6">
        <p class="mx-auto max-w-7xl px-4 text-center text-sm text-muted-foreground sm:px-6 lg:px-8">
            &copy; {{ date('Y') }} Banjar Custome. Hak cipta dilindungi.
        </p>
    </div>
</footer>
