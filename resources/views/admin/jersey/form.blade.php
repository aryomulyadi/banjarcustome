<x-layouts.admin :title="$entry ? 'Edit Data Jersey' : 'Tambah Data Jersey'">

    <a href="{{ route('admin.jersey.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Kembali ke Data Jersey
    </a>

    <form
        method="POST"
        action="{{ $entry ? route('admin.jersey.update', $entry) : route('admin.jersey.store') }}"
        class="mt-4 max-w-2xl"
    >
        @csrf
        @if ($entry)
            @method('PUT')
        @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="space-y-1.5">
                <x-label for="tanggal_masuk">Tanggal Masuk *</x-label>
                <x-input id="tanggal_masuk" name="tanggal_masuk" type="date" value="{{ old('tanggal_masuk', $entry?->tanggal_masuk?->format('Y-m-d')) }}" required />
                @error('tanggal_masuk') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="desainer">Desainer *</x-label>
                <x-select id="desainer" name="desainer" required>
                    <option value="">Pilih desainer…</option>
                    @foreach ($desainers as $name)
                        <option value="{{ $name }}" @selected(old('desainer', $entry?->desainer) === $name)>{{ $name }}</option>
                    @endforeach
                </x-select>
                @error('desainer') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                <p class="text-xs text-muted-foreground">Kelola pilihan di <a href="{{ route('admin.master.index') }}" class="text-primary hover:underline">Data Master</a>.</p>
            </div>

            <div class="space-y-1.5 sm:col-span-2">
                <x-label for="customer">Nama / No. Customer *</x-label>
                <x-input id="customer" name="customer" value="{{ old('customer', $entry?->customer) }}" required maxlength="160" placeholder="cth. ARBI PAIJO - 1810" />
                @error('customer') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="deadline">Deadline</x-label>
                <x-input id="deadline" name="deadline" type="date" value="{{ old('deadline', $entry?->deadline?->format('Y-m-d')) }}" />
                @error('deadline') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                <p class="text-xs text-muted-foreground">Sisa hari dihitung otomatis — "TERLAMBAT" bila lewat.</p>
            </div>

            <div class="space-y-1.5">
                <x-label for="jenis_kerah">Jenis Kerah</x-label>
                <x-select id="jenis_kerah" name="jenis_kerah">
                    <option value="">Pilih kerah…</option>
                    @foreach ($collars as $name)
                        <option value="{{ $name }}" @selected(old('jenis_kerah', $entry?->jenis_kerah) === $name)>{{ $name }}</option>
                    @endforeach
                </x-select>
                @error('jenis_kerah') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="jenis_bahan">Jenis Bahan</x-label>
                <x-select id="jenis_bahan" name="jenis_bahan">
                    <option value="">Pilih bahan…</option>
                    @foreach ($fabrics as $name)
                        <option value="{{ $name }}" @selected(old('jenis_bahan', $entry?->jenis_bahan) === $name)>{{ $name }}</option>
                    @endforeach
                </x-select>
                @error('jenis_bahan') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="model">Model</x-label>
                <x-select id="model" name="model">
                    <option value="">Pilih model…</option>
                    @foreach ($models as $name)
                        <option value="{{ $name }}" @selected(old('model', $entry?->model) === $name)>{{ $name }}</option>
                    @endforeach
                </x-select>
                @error('model') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5">
                <x-label for="status">Status *</x-label>
                <x-select id="status" name="status" required>
                    @foreach ($statuses as $name)
                        <option value="{{ $name }}" @selected(old('status', $entry?->status ?? \App\Models\JerseyData::STATUS_PROSES_DESAIN) === $name)>{{ $name }}</option>
                    @endforeach
                </x-select>
                @error('status') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="space-y-1.5 sm:col-span-2">
                <x-label for="catatan">Catatan</x-label>
                <x-textarea id="catatan" name="catatan" rows="3" maxlength="2000" placeholder="cth. Menunggu DP, revisi, prioritas…">{{ old('catatan', $entry?->catatan) }}</x-textarea>
                @error('catatan') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-5 flex gap-3">
            <x-button type="submit">{{ $entry ? 'Simpan Perubahan' : 'Tambah Data' }}</x-button>
            <x-button href="{{ route('admin.jersey.index') }}" variant="outline">Batal</x-button>
        </div>
    </form>

</x-layouts.admin>
