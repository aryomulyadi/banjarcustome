<x-layouts.admin :title="'Edit Opsi Jersey'">

    <a href="{{ route('admin.master.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Kembali ke Data Master
    </a>

    <p class="mt-3 text-sm text-muted-foreground">
        {{ $option->kind === \App\Models\MasterOption::KIND_COLLAR ? 'Kerah' : 'Bahan' }} jersey — perubahan langsung tampil di Katalog Jersey dan kalkulator.
    </p>

    <form method="POST" action="{{ route('admin.master.update', $option) }}" enctype="multipart/form-data" class="mt-4 max-w-xl">
        @csrf
        @method('PATCH')

        <div class="space-y-4">
            <div class="space-y-1.5">
                <x-label for="name">Nama opsi</x-label>
                <x-input id="name" name="name" value="{{ old('name', $option->name) }}" required maxlength="120" />
                @error('name') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="space-y-1.5">
                    <x-label for="fee">Biaya upgrade per pcs (Rp)</x-label>
                    <x-input id="fee" name="fee" type="number" min="0" max="1000000000" value="{{ old('fee', $option->fee) }}" required />
                    @error('fee') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>
                <div class="space-y-1.5">
                    <x-label for="sort">Urutan di katalog</x-label>
                    <x-input id="sort" name="sort" type="number" min="0" max="1000000" value="{{ old('sort', $option->sort) }}" />
                    @error('sort') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="space-y-1.5">
                <x-label for="image">Foto opsi (jpg/png/webp, maks 2 MB)</x-label>
                @if ($option->image)
                    <div class="mb-2">
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::url($option->image) }}"
                            alt="{{ $option->name }}"
                            class="h-24 w-24 rounded-lg border border-border object-cover"
                        >
                        <p class="mt-1 text-xs text-muted-foreground">Foto saat ini — pilih file baru untuk mengganti.</p>
                    </div>
                @endif
                <input
                    id="image"
                    type="file"
                    name="image"
                    accept="image/jpeg,image/png,image/webp"
                    class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-secondary file:px-3 file:py-2 file:text-sm file:font-medium file:text-foreground hover:file:bg-secondary/80"
                >
                @error('image') <p class="text-xs text-destructive" role="alert">{{ $message }}</p> @enderror
            </div>

            <label class="flex cursor-pointer items-center gap-2.5 text-sm">
                <input
                    type="checkbox"
                    name="active"
                    value="1"
                    class="h-4 w-4 rounded border-input"
                    @checked(old('active', $option->active))
                >
                <span>Tampil di Katalog Jersey</span>
            </label>
        </div>

        <div class="mt-5 flex gap-3">
            <x-button type="submit">Simpan Perubahan</x-button>
            <x-button href="{{ route('admin.master.index') }}" variant="outline">Batal</x-button>
        </div>
    </form>

</x-layouts.admin>
