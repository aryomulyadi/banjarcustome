<x-layouts.admin :title="'Data Master'">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted-foreground">Master data type, bahan, dan warna dipakai pada kartu spesifikasi pesanan serta memvalidasi kolom saat impor Excel.</p>
    </div>

    @php
        $labels = [
            'type' => 'Type',
            'material' => 'Bahan',
            'color' => 'Warna',
        ];
        $descriptions = [
            'type' => 'Jenis barang, misalnya Kaos Polos, Hoodie.',
            'material' => 'Bahan kain/produk, misalnya Cotton Combed 30s.',
            'color' => 'Pilihan warna, swatch opsional heksadesimal.',
        ];
    @endphp

    <div class="mt-5 grid gap-5 lg:grid-cols-3">
        @foreach ($grouped as $kind => $items)
            <div class="overflow-hidden rounded-xl border border-border bg-card">
                <div class="border-b border-border px-5 py-3">
                    <h2 class="text-sm font-semibold">{{ $labels[$kind] ?? $kind }}</h2>
                    <p class="text-xs text-muted-foreground">{{ $descriptions[$kind] ?? '' }}</p>
                </div>

                <div class="px-5 py-4">
                    <form method="POST" action="{{ route('admin.master.store') }}" class="flex flex-col gap-2 sm:flex-row">
                        @csrf
                        <input type="hidden" name="kind" value="{{ $kind }}">
                        <x-input name="name" value="{{ old('kind') === $kind ? old('name') : '' }}" placeholder="Nama {{ $labels[$kind] ?? 'data' }}…" class="flex-1" aria-label="Nama {{ $labels[$kind] ?? 'data' }}" />
                        @if ($kind === 'color')
                            <x-input name="hex" value="{{ old('hex') }}" placeholder="#a3e635" class="sm:w-28" aria-label="Kode warna heksadesimal" />
                        @endif
                        <x-button type="submit">Tambah</x-button>
                    </form>

                    @if ($errors->has('name') && old('kind') === $kind)
                        <p class="mt-2 text-xs text-destructive">{{ $errors->first('name') }}</p>
                    @elseif ($errors->has('hex') && old('kind') === $kind)
                        <p class="mt-2 text-xs text-destructive">{{ $errors->first('hex') }}</p>
                    @endif

                    <div class="mt-4">
                        @if ($items->isEmpty())
                            <p class="py-3 text-center text-xs text-muted-foreground">Belum ada data.</p>
                        @else
                            <ul class="divide-y divide-border">
                                @foreach ($items as $option)
                                    <li class="flex items-center justify-between gap-2 py-2 text-sm">
                                        <span class="flex items-center gap-2">
                                            @if ($option->hex)
                                                <span class="inline-block h-3.5 w-3.5 rounded-full border border-border" style="background: {{ $option->hex }}"></span>
                                            @endif
                                            {{ $option->name }}
                                        </span>
                                        <form method="POST" action="{{ route('admin.master.destroy', $option) }}" onsubmit="return confirm('Hapus {{ $option->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-destructive/40 px-2.5 py-1 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10">
                                                Hapus
                                            </button>
                                        </form>
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
