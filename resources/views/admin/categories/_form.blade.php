@php($category = $category ?? null)

<div class="max-w-xl space-y-5">
    <div class="space-y-2">
        <x-label for="name">Nama Kategori *</x-label>
        <x-input id="name" name="name" value="{{ old('name', $category?->name) }}" required placeholder="cth. Jersey Printing" />
    </div>

    <div class="space-y-2">
        <x-label for="description">Deskripsi</x-label>
        <x-textarea id="description" name="description" rows="3" placeholder="Deskripsi singkat kategori (opsional)…">{{ old('description', $category?->description) }}</x-textarea>
        <p class="text-xs text-muted-foreground">Slug dibuat otomatis dari nama.</p>
    </div>
</div>
