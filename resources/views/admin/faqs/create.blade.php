<x-layouts.admin :title="'Tambah FAQ'">

    <a href="{{ route('admin.faqs.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Kembali ke daftar FAQ
    </a>

    <form method="POST" action="{{ route('admin.faqs.store') }}" class="mt-4">
        @csrf

        @include('admin.faqs._form')

        <div class="mt-5 flex gap-3">
            <x-button type="submit">Simpan FAQ</x-button>
            <x-button href="{{ route('admin.faqs.index') }}" variant="outline">Batal</x-button>
        </div>
    </form>

</x-layouts.admin>
