@php
    $product = $product ?? null;
    $currentColors = $product
        ? $product->colors->map(fn ($color) => ['name' => $color->name, 'hex' => $color->hex])->values()->all()
        : [];
    $currentTiers = $product?->price_tiers ?? [];

    // Saat validasi gagal, pertahankan input admin (bukan nilai DB lama).
    $formColors = old('colors', $currentColors);
    $formTiers = old('price_tiers', $currentTiers);

    if (! is_array($formColors)) {
        $formColors = $currentColors;
    }

    if (! is_array($formTiers)) {
        $formTiers = $currentTiers;
    }

    $jsonFlags = JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT;
    $colorsJson = json_encode(array_values($formColors), $jsonFlags) ?: '[]';
    $tiersJson = json_encode(array_values($formTiers), $jsonFlags) ?: '[]';
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
                    <x-select
                        id="category_id"
                        name="category_id"
                        required
                    >
                        <option value="">Pilih kategori…</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" {{ (string) old('category_id', $product?->category_id) === (string) $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </x-select>
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
            tiers: {{ $tiersJson }},
            add() { this.tiers.push({ min_qty: "", unit_price: "" }); },
            remove(index) { this.tiers.splice(index, 1); },
        }'>
            <div class="flex items-center justify-between gap-3">
                <div>
                    <h2 class="text-base font-semibold">Harga Kalkulator (Admin)</h2>
                    <p class="mt-0.5 text-xs text-muted-foreground">Dipakai halaman /admin/kalkulator. Pembeli tetap melihat "Estimasi Harga" di atas.</p>
                </div>
                <x-button type="button" variant="secondary" size="sm" @click="add()">+ Tangga</x-button>
            </div>

            <div class="mt-4 space-y-4">
                <div class="space-y-2">
                    <x-label for="base_price">Harga dasar (Rp / pcs)</x-label>
                    <x-input id="base_price" name="base_price" type="number" min="0" value="{{ old('base_price', $product?->base_price) }}" placeholder="cth. 45000" />
                    @error('base_price')
                        <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-3">
                    <p class="text-sm font-medium">Tangga jumlah (opsional)</p>

                    <template x-for="(tier, index) in tiers" :key="index">
                        <div class="flex flex-wrap items-center gap-3 rounded-md border border-border p-3">
                            <label class="shrink-0 text-xs text-muted-foreground" :for="`tier-min-${index}`">Min qty</label>
                            <input
                                type="number"
                                min="1"
                                :id="`tier-min-${index}`"
                                :name="`price_tiers[${index}][min_qty]`"
                                x-model="tier.min_qty"
                                placeholder="cth. 50"
                                class="flex h-10 w-28 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring sm:text-sm"
                            >
                            <label class="shrink-0 text-xs text-muted-foreground" :for="`tier-price-${index}`">Harga / pcs</label>
                            <input
                                type="number"
                                min="0"
                                :id="`tier-price-${index}`"
                                :name="`price_tiers[${index}][unit_price]`"
                                x-model="tier.unit_price"
                                placeholder="cth. 40000"
                                class="flex h-10 w-36 rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring sm:text-sm"
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

                    @error('price_tiers')
                        <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                    @enderror
                    @error('price_tiers.*.min_qty')
                        <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                    @enderror
                    @error('price_tiers.*.unit_price')
                        <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                    @enderror

                    <p x-show="tiers.length === 0" x-cloak class="rounded-md border border-dashed border-border px-4 py-4 text-center text-sm text-muted-foreground">
                        Belum ada tangga. Tanpa tangga, kalkulator memakai harga dasar.
                    </p>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-border bg-card p-5" x-data='{
            colors: {{ $colorsJson }},
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
                            class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-base shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring sm:text-sm"
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
                <p class="mt-2 text-xs text-muted-foreground">Gambar utama. Pilih file baru untuk mengganti.</p>
            @endif

            <div class="mt-4 space-y-2">
                <x-label for="image">Unggah gambar utama (jpg/png/webp, maks 2 MB)</x-label>
                <input
                    id="image"
                    name="image"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-secondary/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                >
            </div>

            <p class="mt-3 text-xs text-muted-foreground">Biarkan kosong jika tidak ingin mengubah gambar utama. Tanpa gambar, situs menampilkan placeholder abu-abu.</p>

            @if ($product && $product->images->isNotEmpty())
                <div class="mt-5 border-t border-border pt-4">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-medium">Galeri ({{ $product->images->count() }} foto)</p>
                        <span class="text-[11px] text-muted-foreground">Maks 5 foto</span>
                    </div>
                    <p class="mt-0.5 text-xs text-muted-foreground">Tampil sebagai carousel di halaman produk. Centang "Hapus" untuk membuang foto.</p>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach ($product->images as $galleryImage)
                            <label class="cursor-pointer overflow-hidden rounded-md border border-border transition-colors hover:border-primary/50">
                                <img src="{{ Storage::url($galleryImage->image) }}" alt="{{ $galleryImage->alt ?: $product->title }}" class="aspect-square w-full object-cover">
                                <span class="flex items-center justify-center gap-1.5 border-t border-border bg-card px-1 py-1.5 text-[11px] font-medium text-destructive">
                                    <input
                                        type="checkbox"
                                        name="remove_images[]"
                                        value="{{ $galleryImage->id }}"
                                        class="h-3.5 w-3.5 rounded border-input"
                                        {{ in_array($galleryImage->id, array_map('intval', old('remove_images', []))) ? 'checked' : '' }}
                                    >
                                    Hapus
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-5 space-y-2">
                <x-label for="images">Tambah ke galeri (boleh pilih lebih dari 1 file, maks 5 foto)</x-label>
                <input
                    id="images"
                    name="images[]"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    multiple
                    class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-secondary/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                >
                @error('images')
                    <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                @enderror
                @error('images.*')
                    <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                @enderror
                @error('remove_images.*')
                    <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <p class="mt-3 text-xs text-muted-foreground">Foto galeri dioptimalkan otomatis (webp) seperti gambar utama.</p>
        </div>
    </div>
</div>
