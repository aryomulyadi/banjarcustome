<x-layouts.admin :title="'Galeri'">

    <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">Foto karya & hasil produksi yang tampil di halaman Galeri.</p>
        <x-button href="{{ route('admin.galleries.create') }}">Tambah Foto</x-button>
    </div>

    @if ($galleries->isEmpty())
        <p class="mt-5 rounded-xl border border-border bg-card px-5 py-10 text-center text-sm text-muted-foreground">Belum ada foto galeri.</p>
    @else
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            @foreach ($galleries as $gallery)
                <div class="overflow-hidden rounded-xl border border-border bg-card">
                    <div class="aspect-[4/3] bg-muted">
                        @if ($gallery->image)
                            <img src="{{ Storage::url($gallery->image) }}" alt="{{ $gallery->title }}" class="h-full w-full object-cover">
                        @else
                            <x-placeholder-image label="Tanpa Gambar" class="h-full w-full" />
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="truncate text-sm font-semibold">{{ $gallery->title }}</p>
                        <p class="mt-0.5 line-clamp-2 text-xs text-muted-foreground">{{ $gallery->caption ?: '—' }}</p>
                        @if ($gallery->category)
                            <span class="mt-2 inline-block rounded-full bg-secondary px-2 py-0.5 text-[11px] font-medium text-secondary-foreground">{{ $gallery->category }}</span>
                        @endif
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('admin.galleries.edit', $gallery) }}" class="flex-1 rounded-md border border-border px-2.5 py-1.5 text-center text-xs font-medium transition-colors hover:bg-secondary">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('admin.galleries.destroy', $gallery) }}" onsubmit="return confirm('Hapus foto {{ $gallery->title }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-md border border-destructive/40 px-2.5 py-1.5 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="mt-5">
        {{ $galleries->links() }}
    </div>

</x-layouts.admin>
