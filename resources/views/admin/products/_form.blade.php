@php
    $product = $product ?? null;
    $currentColors = $product
        ? $product->colors->map(fn ($color) => ['name' => $color->name, 'hex' => $color->hex])->values()->all()
        : [];
@endphp

<div class="grid gap-5 lg:grid-cols-3">
    <div class="space-y-5 lg:col-span-2">
        <div class="rounded-xl border border-border bg-card p-5">
            <div class="space-y-4">
                <div class="space-y-2">
                    <x-label for="title">Nama Produk *</x-label>
                    <x-input id="title" name="title" value="{{ old('title', $product?->title) }}" required placeholder="cth. Kaos Promosi Custom" />
                </div>

                <div class="space-y-2">
                    <x-label for="category_id">Kategori *</x-label>
                    <select
                        id="category_id"
                        name="category_id"
                        required
                        class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                        <option value="">Pilih kategori…</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('category_id', $product?->category_id) === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    <x-label for="description">Deskripsi</x-label>
                    <x-textarea id="description" name="description" rows="4" placeholder="Deskripsi singkat produk…">{{ old('description', $product?->description) }}</x-textarea>
                </div>

                <div class="space-y-2">
                    <x-label for="price_estimate">Estimasi Harga</x-label>
                    <x-input id="price_estimate" name="price_estimate" value="{{ old('price_estimate', $product?->price_estimate) }}" placeholder="cth. Rp35.000 – Rp50.000 / pcs" />
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-border bg-card p-5" x-data='{
            colors: {{ json_encode($currentColors) }},
            add() { this.colors.push({ name: "",hex: "#F59E0B" }); },
            remove(index) { this.colors.splice(index, 1); },
        }'>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold">Warna Produk</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Palet warna yang bisa dipilih calon pembeli di katalog.</p>
                </div>
                <x-button type="button" variant="secondary" size="sm" @click="add()">+ Tambah</x-button>
            </div>

            <div class="mt-4 space-y-3">
                <template x-for="(color, index) in colors" :key="index">
                    <div class="flex items-center gap-3 rounded-md border border-border p-3">
                        <input
                            type="color"
                            :name="`colors[${index}][hex]`"
                            x-model="color.hex"
                            class="h-10 w-12 shrink-0 cursor-pointer rounded-md border border-input bg-transparent p-1"
                        >
                        <input
                            type="text"
                            :name="`colors[${index}][name]`"
                            x-model="color.name"
                            placeholder="Nama warna (cth. Navy)"
                            class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        >
                        <button
                            type="button"
                            @click="remove(index)"
                            class="shrink-0 rounded-md border border-destructive/40 px-3 py-2 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10"
                        >
                            Hapus
                        </button>
                    </div>
                </template>

                <p x-show="colors.length === 0" x-cloak class="rounded-md border border-dashed border-border px-4 py-4 text-center text-sm text-muted-foreground">
                    Belum ada warna. Klik "+ Tambah" untuk membuat palet.
                </p>
            </div>
        </div>
    </div>

    <div class="space-y-5">
        <div class="rounded-xl border border-border bg-card p-5">
            <h2 class="text-base font-semibold">Gambar Produk</h2>

            @if ($product?->image)
                <div class="mt-3 overflow-hidden rounded-md border border-border">
                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->title }}" class="aspect-[4/3] w-full object-cover">
                </div>
                <p class="mt-2 text-xs text-muted-foreground">Gambar saat ini. Pilih file baru untuk mengganti.</p>
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

            <p class="mt-3 text-xs text-muted-foreground">Biarkan kosong jika tidak ingin mengubah gambar. Tanpa gambar, situs menampilkan placeholder abu-abu.</p>
        </div>
    </div>
</div>
