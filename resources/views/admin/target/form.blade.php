<x-layouts.admin :title="$entry ? 'Edit Data Target' : 'Tambah Data Target'">

    <a href="{{ route('admin.target.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Kembali ke Data Target
    </a>

    <form
        method="POST"
        action="{{ $entry ? route('admin.target.update', $entry) : route('admin.target.store') }}"
        class="mt-4 max-w-2xl"
    >
        @csrf
        @if ($entry)
            @method('PUT')
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1.5 sm:col-span-2">
                <x-label for="customer">Customer *</x-label>
                <x-input id="customer" name="customer" value="{{ old('customer', $entry?->customer) }}" required maxlength="160" placeholder="cth. ARBI PAIJO 1810" />
                @error('customer') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="jenis">Jenis</x-label>
                <x-select id="jenis" name="jenis">
                    <option value="">Pilih jenis…</option>
                    @foreach ($jenisList as $name)
                        <option value="{{ $name }}" @selected(old('jenis', $entry?->jenis) === $name)>{{ $name }}</option>
                    @endforeach
                </x-select>
                @error('jenis') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="waktu_pengambilan">Waktu Pengambilan</x-label>
                <x-input id="waktu_pengambilan" name="waktu_pengambilan" type="date" value="{{ old('waktu_pengambilan', $entry?->waktu_pengambilan?->format('Y-m-d')) }}" />
                @error('waktu_pengambilan') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="type">Type</x-label>
                <x-select id="type" name="type">
                    <option value="">Pilih type…</option>
                    @foreach ($typeList as $name)
                        <option value="{{ $name }}" @selected(old('type', $entry?->type) === $name)>{{ $name }}</option>
                    @endforeach
                </x-select>
                @error('type') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="bahan">Bahan</x-label>
                <x-select id="bahan" name="bahan">
                    <option value="">Pilih bahan…</option>
                    @foreach ($bahanList as $name)
                        <option value="{{ $name }}" @selected(old('bahan', $entry?->bahan) === $name)>{{ $name }}</option>
                    @endforeach
                </x-select>
                @error('bahan') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="warna">Warna</x-label>
                <x-select id="warna" name="warna">
                    <option value="">Pilih warna…</option>
                    @foreach ($warnaList as $name)
                        <option value="{{ $name }}" @selected(old('warna', $entry?->warna) === $name)>{{ $name }}</option>
                    @endforeach
                </x-select>
                @error('warna') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="total">Total (jumlah pcs) *</x-label>
                <x-input id="total" name="total" type="number" min="0" max="1000000000" value="{{ old('total', $entry?->total ?? 0) }}" required />
                @error('total') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="pesanan_bahan">Pesan Bahan *</x-label>
                <x-select id="pesanan_bahan" name="pesanan_bahan" required>
                    @foreach (['NOK', 'OK'] as $flag)
                        <option value="{{ $flag }}" @selected(old('pesanan_bahan', $entry?->pesanan_bahan ?? 'NOK') === $flag)>{{ $flag }}</option>
                    @endforeach
                </x-select>
                @error('pesanan_bahan') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="progres_produksi">Produksi *</x-label>
                <x-select id="progres_produksi" name="progres_produksi" required>
                    @foreach (['NOK', 'OK'] as $flag)
                        <option value="{{ $flag }}" @selected(old('progres_produksi', $entry?->progres_produksi ?? 'NOK') === $flag)>{{ $flag }}</option>
                    @endforeach
                </x-select>
                @error('progres_produksi') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="finish">Finish *</x-label>
                <x-select id="finish" name="finish" required>
                    @foreach (['NOK', 'OK'] as $flag)
                        <option value="{{ $flag }}" @selected(old('finish', $entry?->finish ?? 'NOK') === $flag)>{{ $flag }}</option>
                    @endforeach
                </x-select>
                @error('finish') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-5 flex gap-3">
            <x-button type="submit">{{ $entry ? 'Simpan Perubahan' : 'Tambah Data' }}</x-button>
            <x-button href="{{ route('admin.target.index') }}" variant="outline">Batal</x-button>
        </div>
    </form>

</x-layouts.admin>
