<x-layouts.admin :title="'Kategori'">

    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted-foreground">Kategori tampil sebagai menu navigasi di header dan footer.</p>
        <div class="flex gap-2">
            <form method="GET" action="{{ route('admin.categories.index') }}" class="flex gap-2">
                <x-input name="q" value="{{ $search }}" placeholder="Cari kategori…" class="w-full sm:w-48" aria-label="Cari kategori" />
                <x-button type="submit" variant="secondary">Cari</x-button>
            </form>
            <x-button href="{{ route('admin.categories.create') }}">Tambah Kategori</x-button>
        </div>
    </div>

    <div class="mt-5 overflow-hidden rounded-xl border border-border bg-card">
        @if ($categories->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-muted-foreground">Belum ada kategori.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                            <th class="px-5 py-3 font-medium">Nama</th>
                            <th class="px-5 py-3 font-medium">Slug</th>
                            <th class="px-5 py-3 font-medium">Produk</th>
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr class="border-b border-border last:border-0 hover:bg-secondary/50">
                                <td class="px-5 py-3 font-medium">{{ $category->name }}</td>
                                <td class="px-5 py-3 text-muted-foreground">{{ $category->slug }}</td>
                                <td class="px-5 py-3">{{ $category->products_count }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-md border border-border px-2.5 py-1 text-xs font-medium transition-colors hover:bg-secondary">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Hapus kategori {{ $category->name }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-md border border-destructive/40 px-2.5 py-1 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="mt-5">
        {{ $categories->links() }}
    </div>

</x-layouts.admin>
