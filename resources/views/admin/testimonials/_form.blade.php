@php($testimonial = $testimonial ?? null)

<div class="max-w-xl space-y-5">
    <div class="grid gap-5 sm:grid-cols-2">
        <div class="space-y-2">
            <x-label for="name">Nama Pelanggan *</x-label>
            <x-input id="name" name="name" value="{{ old('name', $testimonial?->name) }}" required placeholder="cth. Ahmad" />
        </div>

        <div class="space-y-2">
            <x-label for="role">Peran / Komunitas</x-label>
            <x-input id="role" name="role" value="{{ old('role', $testimonial?->role) }}" placeholder="cth. Koordinator Tim" />
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div class="space-y-2">
            <x-label for="city">Kota</x-label>
            <x-input id="city" name="city" value="{{ old('city', $testimonial?->city) }}" placeholder="cth. Banjarmasin" />
        </div>

        <div class="space-y-2">
            <x-label for="rating">Rating (1-5) *</x-label>
            <select id="rating" name="rating" class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring">
                @for ($i = 5; $i >= 1; $i--)
                    <option value="{{ $i }}" @selected((int) old('rating', $testimonial?->rating ?? 5) === $i)>{{ $i }} / 5</option>
                @endfor
            </select>
        </div>
    </div>

    <div class="space-y-2">
        <x-label for="content">Isi Testimoni *</x-label>
        <x-textarea id="content" name="content" rows="4" required placeholder="Ceritakan pengalaman pelanggan memesan di Banjar Custome…">{{ old('content', $testimonial?->content) }}</x-textarea>
    </div>

    <div class="space-y-2">
        <x-label for="photo">Foto Pelanggan (opsional)</x-label>

        @if ($testimonial?->photo)
            <div class="overflow-hidden rounded-md border border-border" style="width: 3.25rem; height: 3.25rem;">
                <img src="{{ Storage::url($testimonial->photo) }}" alt="{{ $testimonial->name }}" class="h-full w-full object-cover">
            </div>
            <p class="text-xs text-muted-foreground">Foto saat ini. Pilih file baru untuk mengganti.</p>
        @endif

        <input
            id="photo"
            name="photo"
            type="file"
            accept="image/jpeg,image/png,image/webp"
            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-secondary/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
        >
        <p class="text-xs text-muted-foreground">jpg/png/webp, maks 2 MB. Tanpa foto, beranda menampilkan inisial nama.</p>
    </div>

    <label class="flex cursor-pointer items-center gap-2 text-sm font-medium">
        <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-input accent-[color:var(--color-primary)]" @checked(old('is_active', $testimonial?->is_active ?? true))>
        Tampilkan di beranda
    </label>
</div>
