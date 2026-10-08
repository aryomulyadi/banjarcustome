<x-layouts.app
    :title="'Galeri Hasil Produksi — Banjar Custome'"
    :description="'Lihat hasil produksi sablon dan konveksi Banjar Custome: kaos komunitas, seragam sekolah, jersey, dan merchandise untuk Banjarmasin dan sekitarnya.'"
>

    <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <nav class="mb-6 flex items-center gap-2 text-sm text-muted-foreground">
            <a href="{{ route('home') }}" class="hover:text-primary">Beranda</a>
            <span>/</span>
            <span class="text-foreground">Galeri</span>
        </nav>

        <x-jsonld-breadcrumbs :items="array_values(array_filter([
            ['name' => 'Beranda', 'url' => route('home')],
            ['name' => 'Galeri', 'url' => route('galeri')],
            $activeCategory ? ['name' => $activeCategory, 'url' => route('galeri', ['kategori' => $activeCategory])] : null,
        ]))" />

        <div class="mb-8">
            <h1 class="text-2xl font-bold sm:text-3xl">Galeri Hasil Produksi</h1>
            <p class="mt-1 text-sm text-muted-foreground">Dokumentasi pesanan untuk komunitas, sekolah, kantor, dan event.</p>
        </div>

        <div class="mb-6 flex flex-wrap gap-2">
            <a
                href="{{ route('galeri') }}"
                class="rounded-full px-4 py-1.5 text-sm font-medium transition-colors {{ $activeCategory === null ? 'bg-primary text-primary-foreground' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
            >
                Semua
            </a>
            @foreach ($categories as $category)
                <a
                    href="{{ route('galeri', ['kategori' => $category]) }}"
                    class="rounded-full px-4 py-1.5 text-sm font-medium transition-colors {{ $activeCategory === $category ? 'bg-primary text-primary-foreground' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
                >
                    {{ $category }}
                </a>
            @endforeach
        </div>

        @if ($galleries->isEmpty())
            <div class="rounded-xl border border-dashed border-border p-12 text-center">
                <p class="font-medium">Belum ada foto di kategori ini</p>
                <a href="{{ route('galeri') }}" class="mt-4 inline-flex h-9 items-center rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground">
                    Lihat Semua Galeri
                </a>
            </div>
        @else
            <div x-data="galleryLightbox">
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($galleries as $index => $gallery)
                        <figure class="group overflow-hidden rounded-xl border border-border bg-card transition-all hover:-translate-y-0.5 hover:shadow-lg">
                            @if ($gallery->image)
                                <a
                                    href="{{ asset('storage/' . $gallery->image) }}"
                                    data-lb
                                    data-title="{{ $gallery->title }}"
                                    class="block"
                                    @click.prevent="open({{ $index }})"
                                >
                                    <x-img :src="$gallery->image" :alt="$gallery->title" width="800" height="600" loading="lazy" decoding="async" class="aspect-[4/3] w-full object-cover transition-transform duration-300 group-hover:scale-105" />
                                </a>
                            @else
                                <x-placeholder-image label="Galeri" class="transition-transform duration-300 group-hover:scale-105" />
                            @endif
                            <figcaption class="p-4">
                                @if ($gallery->category)
                                    <span class="inline-block rounded-full bg-secondary px-2.5 py-0.5 text-xs font-medium text-secondary-foreground">{{ $gallery->category }}</span>
                                @endif
                                <p class="mt-2 text-sm font-semibold">{{ $gallery->title }}</p>
                                @if ($gallery->caption)
                                    <p class="mt-1 line-clamp-2 text-xs text-muted-foreground">{{ $gallery->caption }}</p>
                                @endif
                            </figcaption>
                        </figure>
                    @endforeach
                </div>

                <div class="mt-8">
                    {{ $galleries->links() }}
                </div>

                <dialog
                    x-ref="dialog"
                    @click="if ($event.target === $el) close()"
                    @keydown.left.prevent="prev()"
                    @keydown.right.prevent="next()"
                    class="w-[min(95vw,64rem)] rounded-xl border-0 bg-card p-0 shadow-2xl backdrop:bg-black/80"
                >
                    <div @click.stop class="relative">
                        <button
                            type="button"
                            @click="close()"
                            aria-label="Tutup galeri"
                            class="absolute right-3 top-3 z-10 flex h-9 w-9 items-center justify-center rounded-full bg-background/80 text-lg text-foreground backdrop-blur transition-colors hover:bg-background"
                        >
                            &times;
                        </button>
                        <div class="flex min-h-48 items-center justify-center p-3">
                            <img
                                :src="current?.url"
                                :alt="current?.title"
                                width="800"
                                height="600"
                                class="max-h-[70vh] w-auto max-w-full object-contain"
                            >
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-border px-4 py-3">
                            <button type="button" @click="prev()" aria-label="Foto sebelumnya" class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-foreground transition-colors hover:bg-secondary">
                                &larr;
                            </button>
                            <p class="min-w-0 flex-1 truncate text-center text-sm">
                                <span x-text="current?.title"></span>
                                <span class="text-muted-foreground">(<span x-text="index + 1"></span>/<span x-text="items.length"></span>)</span>
                            </p>
                            <button type="button" @click="next()" aria-label="Foto selanjutnya" class="flex h-9 w-9 items-center justify-center rounded-full border border-border text-foreground transition-colors hover:bg-secondary">
                                &rarr;
                            </button>
                        </div>
                    </div>
                </dialog>
            </div>
        @endif
    </div>

</x-layouts.app>
