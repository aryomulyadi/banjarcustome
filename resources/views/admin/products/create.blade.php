<x-layouts.admin :title="'Tambah Produk'">

    <a href="{{ route('admin.products.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Kembali ke daftar produk
    </a>

    <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="mt-4">
        @csrf

        @include('admin.products._form', ['categories' => $categories])

        <div class="mt-5 flex gap-3">
            <x-button type="submit">Simpan Produk</x-button>
            <x-button href="{{ route('admin.products.index') }}" variant="outline">Batal</x-button>
        </div>
    </form>

</x-layouts.admin>
