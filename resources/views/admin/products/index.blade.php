<x-layouts.admin :title="'Produk'">

    <div class="flex items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">Kelola katalog produk yang tampil di situs publik.</p>
        <x-button href="{{ route('admin.products.create') }}">Tambah Produk</x-button>
    </div>

    <div class="mt-5 overflow-hidden rounded-xl border border-border bg-card">
        @if ($products->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-muted-foreground">Belum ada produk.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                            <th class="px-5 py-3 font-medium">Produk</th>
                            <th class="px-5 py-3 font-medium">Kategori</th>
                            <th class="px-5 py-3 font-medium">Harga</th>
                            <th class="px-5 py-3 font-medium">Warna</th>
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($products as $product)
                            <tr class="border-b border-border last:border-0 hover:bg-secondary/50">
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 overflow-hidden rounded-md border border-border bg-muted">
                                            @if ($product->image)
                                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->title }}" class="h-full w-full object-cover">
                                            @endif
                                        </div>
                                        <div>
                                            <span class="block font-medium">{{ $product->title }}</span>
                                            <a href="{{ route('produk.show', $product->slug) }}" target="_blank" class="text-xs text-muted-foreground hover:text-primary">/produk/{{ $product->slug }}</a>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-3">{{ $product->category?->name ?? '—' }}</td>
                                <td class="px-5 py-3">{{ $product->price_estimate ?: '—' }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-1">
                                        @forelse ($product->colors as $color)
                                            <span class="h-4 w-4 rounded-full border border-border" style="background-color: {{ $color->hex }}" title="{{ $color->name }}"></span>
                                        @empty
                                            <span class="text-muted-foreground">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('admin.products.edit', $product) }}" class="rounded-md border border-border px-2.5 py-1 text-xs font-medium transition-colors hover:bg-secondary">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus produk {{ $product->title }}?')">
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
        {{ $products->links() }}
    </div>

</x-layouts.admin>
