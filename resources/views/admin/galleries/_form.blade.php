@php($gallery = $gallery ?? null)

<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div class="rounded-xl border border-border bg-card p-5">
            <div class="space-y-4">
                <div class="space-y-2">
                    <x-label for="title">Judul *</x-label>
                    <x-input id="title" name="title" value="{{ old('title', $gallery?->title) }}" required placeholder="cth. Jersey Futsal Tim Garuda" />
                </div>

                <div class="space-y-2">
                    <x-label for="caption">Caption</x-label>
                    <x-textarea id="caption" name="caption" rows="3" placeholder="Deskripsi singkat foto (opsional)…">{{ old('caption', $gallery?->caption) }}</x-textarea>
                </div>

                <div class="space-y-2">
                    <x-label for="category">Kategori Teks</x-label>
                    <x-input id="category" name="category" value="{{ old('category', $gallery?->category) }}" placeholder="cth. Jersey, Kaos, Kemeja (opsional)" />
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="rounded-xl border border-border bg-card p-5">
            <h2 class="text-base font-semibold">Foto</h2>

            @if ($gallery?->image)
                <div class="mt-3 overflow-hidden rounded-md border border-border">
                    <img src="{{ Storage::url($gallery->image) }}" alt="{{ $gallery->title }}" class="aspect-[4/3] w-full object-cover">
                </div>
                <p class="mt-2 text-xs text-muted-foreground">Foto saat ini. Pilih file baru untuk mengganti.</p>
            @endif

            <div class="mt-4 space-y-2">
                <x-label for="image">Unggah gambar (jpg/png/webp, maks 2 MB)</x-label>
                <input
                    id="image"
                    name="image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-secondary/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                >
            </div>
        </div>
    </div>
</div>
