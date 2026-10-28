<x-layouts.admin :title="'Data Master'">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted-foreground">Master data type, bahan, warna, kerah & bahan jersey dipakai pada kartu spesifikasi pesanan, katalog jersey, serta memvalidasi kolom saat impor Excel. Desainer, model, status jersey, jenis & type target dipakai dropdown pada modul Data Jersey dan Data Target.</p>
    </div>

    @php
        $labels = [
            'type' => 'Type',
            'material' => 'Bahan',
            'color' => 'Warna',
            'collar' => 'Kerah Jersey',
            'fabric' => 'Bahan Jersey',
            'designer' => 'Desainer',
            'jersey_model' => 'Model Jersey',
            'jersey_status' => 'Status Jersey',
            'target_line' => 'Jenis Target',
            'target_type' => 'Type Target',
        ];
        $descriptions = [
            'type' => 'Jenis barang, misalnya Kaos Polos, Hoodie.',
            'material' => 'Bahan kain/produk, misalnya Cotton Combed 30s.',
            'color' => 'Pilihan warna, swatch opsional heksadesimal.',
            'collar' => 'Upgrade kerah jersey: biaya per pcs + foto tampil di Katalog Jersey.',
            'fabric' => 'Upgrade bahan jersey: biaya per pcs + foto tampil di Katalog Jersey.',
            'designer' => 'Tim desainer, dipakai dropdown Data Jersey.',
            'jersey_model' => 'Model potongan jersey, dipakai dropdown Data Jersey.',
            'jersey_status' => 'Alur status operasional Data Jersey.',
            'target_line' => 'Jenis produk target/produksi, dipakai dropdown Data Target.',
            'target_type' => 'Type potongan target/produksi, dipakai dropdown Data Target.',
        ];
        $errorKeys = ['name', 'hex', 'fee', 'image'];
    @endphp

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        @foreach ($grouped as $kind => $items)
            @php
                $isJersey = \App\Models\MasterOption::isJerseyKind($kind);
            @endphp
            <div class="overflow-hidden rounded-xl border border-border bg-card">
                <div class="border-b border-border px-5 py-3">
                    <h2 class="text-sm font-semibold">{{ $labels[$kind] ?? $kind }}</h2>
                    <p class="text-xs text-muted-foreground">{{ $descriptions[$kind] ?? '' }}</p>
                </div>

                <div class="px-5 py-4">
                    <form
                        method="POST"
                        action="{{ route('admin.master.store') }}"
                        class="flex flex-col gap-2"
                        @if ($isJersey) enctype="multipart/form-data" @endif
                    >
                        @csrf
                        <input type="hidden" name="kind" value="{{ $kind }}">
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <x-input name="name" value="{{ old('kind') === $kind ? old('name') : '' }}" placeholder="Nama {{ $labels[$kind] ?? 'data' }}…" class="flex-1" aria-label="Nama {{ $labels[$kind] ?? 'data' }}" />
                            @if ($kind === 'color')
                                <x-input name="hex" value="{{ old('hex') }}" placeholder="#a3e635" class="sm:w-28" aria-label="Kode warna heksadesimal" />
                            @endif
                            @if ($isJersey)
                                <x-input name="fee" value="{{ old('kind') === $kind ? old('fee') : '' }}" type="number" min="0" placeholder="Biaya +Rp…" class="sm:w-32" aria-label="Biaya upgrade per pcs dalam rupiah" />
                            @endif
                            <x-button type="submit" class="shrink-0">Tambah</x-button>
                        </div>
                        @if ($isJersey)
                            <label class="text-xs text-muted-foreground">
                                Foto opsi (opsional, maks 2 MB)
                                <input
                                    type="file"
                                    name="image"
                                    accept="image/jpeg,image/png,image/webp"
                                    class="mt-1 block w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-foreground hover:file:bg-secondary/80"
                                >
                            </label>
                        @endif
                    </form>

                    @foreach ($errorKeys as $errorKey)
                        @if ($errors->has($errorKey) && old('kind') === $kind)
                            <p class="mt-2 text-xs text-destructive">{{ $errors->first($errorKey) }}</p>
                        @endif
                    @endforeach

                    <div class="mt-4">
                        @if ($items->isEmpty())
                            <p class="py-3 text-center text-xs text-muted-foreground">Belum ada data.</p>
                        @else
                            <ul class="divide-y divide-border">
                                @foreach ($items as $option)
                                    <li class="flex items-center justify-between gap-2 py-2 text-sm">
                                        <span class="flex min-w-0 items-center gap-2">
                                            @if ($option->image)
                                                <img
                                                    src="{{ \Illuminate\Support\Facades\Storage::url($option->image) }}"
                                                    alt="{{ $option->name }}"
                                                    class="h-9 w-9 shrink-0 rounded-md border border-border object-cover"
                                                    loading="lazy"
                                                >
                                            @elseif ($option->hex)
                                                <span class="inline-block h-3.5 w-3.5 rounded-full border border-border" style="background: {{ $option->hex }}"></span>
                                            @endif
                                            <span class="truncate">{{ $option->name }}</span>
                                            @if ($isJersey)
                                                <span class="shrink-0 rounded-full border border-primary/30 bg-primary/5 px-2 py-0.5 text-[11px] font-semibold text-primary">
                                                    {{ $option->fee > 0 ? '+'.number_format($option->fee, 0, ',', '.') : 'Standar' }}
                                                </span>
                                            @endif
                                            @unless ($option->active)
                                                <span class="shrink-0 rounded-full border border-border bg-muted px-2 py-0.5 text-[11px] font-medium text-muted-foreground">Nonaktif</span>
                                            @endunless
                                        </span>
                                        <span class="flex shrink-0 items-center gap-1.5">
                                            <a href="{{ route('admin.master.edit', $option) }}" class="rounded-md border border-border px-2.5 py-1 text-xs font-medium text-muted-foreground transition-colors hover:bg-secondary hover:text-foreground">
                                                Edit
                                            </a>
                                            <form method="POST" action="{{ route('admin.master.destroy', $option) }}" onsubmit="return confirm('Hapus {{ $option->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-md border border-destructive/40 px-2.5 py-1 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10">
                                                    Hapus
                                                </button>
                                            </form>
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</x-layouts.admin>
