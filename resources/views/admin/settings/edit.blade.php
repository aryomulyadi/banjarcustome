<x-layouts.admin :title="'Pengaturan Beranda'">

    <p class="text-sm text-muted-foreground">Atur slide hero, statistik, dan daftar jenis sablon yang tampil di website.</p>

    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mt-5">
        @csrf
        @method('PUT')

        <h2 class="text-base font-semibold">Slide Hero (3 slide)</h2>
        <div class="mt-3 space-y-5">
            @foreach ($slides as $index => $slide)
                <fieldset class="rounded-xl border border-border bg-card p-5">
                    <legend class="px-1 text-sm font-semibold text-primary">Slide {{ $index + 1 }}</legend>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div class="space-y-2">
                            <x-label for="slide_title_{{ $index }}">Judul *</x-label>
                            <x-input id="slide_title_{{ $index }}" name="slides[{{ $index }}][title]" value="{{ old("slides.$index.title", $slide['title']) }}" required />
                        </div>
                        <div class="space-y-2">
                            <x-label for="slide_subtitle_{{ $index }}">Subjudul *</x-label>
                            <x-input id="slide_subtitle_{{ $index }}" name="slides[{{ $index }}][subtitle]" value="{{ old("slides.$index.subtitle", $slide['subtitle']) }}" required />
                        </div>
                    </div>

                    <div class="mt-4 grid gap-5 sm:grid-cols-2">
                        <div class="space-y-2">
                            <x-label for="slide_image_{{ $index }}">Ganti Banner</x-label>
                            <input
                                id="slide_image_{{ $index }}"
                                name="slides[{{ $index }}][image]"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp"
                                class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-sm file:font-medium hover:file:bg-secondary/70 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                            >
                            <p class="text-xs text-muted-foreground">Kosongkan jika tidak diganti. Banner saat ini: <code>{{ $slide['image'] }}</code></p>
                            @error("slides.$index.image") <p class="text-xs text-destructive">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </fieldset>
            @endforeach
        </div>

        <h2 class="mt-8 text-base font-semibold">Statistik (3 kotak)</h2>
        <div class="mt-3 grid gap-5 sm:grid-cols-3">
            @foreach ($stats as $index => $stat)
                <div class="space-y-2">
                    <x-label for="stat_value_{{ $index }}">Nilai {{ $index + 1 }} *</x-label>
                    <x-input id="stat_value_{{ $index }}" name="stats[{{ $index }}][value]" value="{{ old("stats.$index.value", $stat['value']) }}" required placeholder="cth. 500+" />
                    <x-label for="stat_label_{{ $index }}">Label {{ $index + 1 }} *</x-label>
                    <x-input id="stat_label_{{ $index }}" name="stats[{{ $index }}][label]" value="{{ old("stats.$index.label", $stat['label']) }}" required placeholder="cth. Pesanan Selesai" />
                </div>
            @endforeach
        </div>

        <h2 class="mt-8 text-base font-semibold">Jenis Sablon &amp; Teknik</h2>
        <p class="mt-1 text-sm text-muted-foreground">
            Daftar ini dipakai untuk <strong>pilihan "Jenis Sablon" di form pesanan</strong> dan kartu di halaman
            <a href="{{ route('layanan') }}" target="_blank" class="font-medium text-primary hover:underline">Layanan</a>.
        </p>
        <div
            class="mt-3 rounded-xl border border-border bg-card p-5"
            x-data='{
                services: @js(old("services", $services)),
                add() { this.services.push({ title: "", desc: "" }); },
                remove(index) { this.services.splice(index, 1); },
            }'
        >
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold">Daftar layanan (<span x-text="services.length"></span>)</p>
                <button
                    type="button"
                    @click="add()"
                    class="rounded-md border border-border px-3 py-1.5 text-xs font-semibold transition-colors hover:bg-secondary"
                >
                    + Tambah
                </button>
            </div>

            <div class="mt-4 space-y-3">
                <template x-for="(service, index) in services" :key="index">
                    <div class="rounded-md border border-border p-3">
                        <div class="flex items-start gap-3">
                            <div class="flex-1 space-y-2">
                                <input
                                    type="text"
                                    maxlength="60"
                                    :name="`services[${index}][title]`"
                                    x-model="service.title"
                                    placeholder="Judul (maks. 60 karakter, cth. Sablon Plastisol)"
                                    class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                >
                                <textarea
                                    rows="2"
                                    maxlength="255"
                                    :name="`services[${index}][desc]`"
                                    x-model="service.desc"
                                    placeholder="Deskripsi singkat (opsional, maks. 255 karakter)"
                                    class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                ></textarea>
                            </div>
                            <button
                                type="button"
                                @click="remove(index)"
                                class="mt-1 shrink-0 rounded-md border border-destructive/40 px-3 py-1.5 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                </template>

                <p x-show="services.length === 0" x-cloak class="rounded-md border border-dashed border-border px-4 py-4 text-center text-sm text-muted-foreground">
                    Belum ada layanan. Klik "+ Tambah" untuk mengisi daftar.
                </p>
            </div>
            @error('services') <p class="mt-2 text-xs text-destructive">{{ $message }}</p> @enderror
            @error('services.0.title') <p class="mt-2 text-xs text-destructive">{{ $message }}</p> @enderror
        </div>

        <div class="mt-6 flex gap-3">
            <x-button type="submit">Simpan Pengaturan</x-button>
        </div>
    </form>

</x-layouts.admin>
