<x-layouts.admin :title="'Pesanan'">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('admin.orders.index') }}"
                class="rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ ! $activeStatus ? 'bg-primary text-primary-foreground' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
            >
                Semua <span class="opacity-70">({{ $statusCounts['all'] ?? 0 }})</span>
            </a>
            @foreach ($statusLabels as $key => $label)
                <a
                    href="{{ route('admin.orders.index', ['status' => $key]) }}"
                    class="rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ $activeStatus === $key ? 'bg-primary text-primary-foreground' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
                >
                    {{ $label }} <span class="opacity-70">({{ $statusCounts[$key] ?? 0 }})</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex gap-2">
            @if ($activeStatus)
                <input type="hidden" name="status" value="{{ $activeStatus }}">
            @endif
            <x-input name="q" value="{{ $search }}" placeholder="Cari nama / no. WA…" class="w-full lg:w-64" />
            <x-button type="submit" variant="secondary">Cari</x-button>
        </form>
    </div>

    <div class="mt-5 overflow-hidden rounded-xl border border-border bg-card">
        @if ($orders->isEmpty())
            <p class="px-5 py-10 text-center text-sm text-muted-foreground">Tidak ada pesanan yang cocok.</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border text-left text-xs uppercase tracking-wider text-muted-foreground">
                            <th class="px-5 py-3 font-medium">#</th>
                            <th class="px-5 py-3 font-medium">Pelanggan</th>
                            <th class="px-5 py-3 font-medium">Produk</th>
                            <th class="px-5 py-3 font-medium">Qty</th>
                            <th class="px-5 py-3 font-medium">Status</th>
                            <th class="px-5 py-3 font-medium">Tanggal</th>
                            <th class="px-5 py-3 text-right font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="border-b border-border last:border-0 hover:bg-secondary/50">
                                <td class="px-5 py-3">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-primary hover:underline">#{{ $order->id }}</a>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="font-medium">{{ $order->name }}</span>
                                    <span class="block text-xs text-muted-foreground">{{ $order->whatsapp_number }}</span>
                                </td>
                                <td class="px-5 py-3">{{ $order->product?->title ?? 'Custom' }}</td>
                                <td class="px-5 py-3">{{ $order->quantity ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $order->statusLabel() }}</span>
                                </td>
                                <td class="px-5 py-3 text-muted-foreground">{{ $order->created_at->translatedFormat('d M Y') }}</td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        <a
                                            href="{{ $order->customerWhatsappLink() }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="rounded-md border border-border px-2.5 py-1 text-xs font-medium transition-colors hover:bg-secondary"
                                        >
                                            Chat WA
                                        </a>
                                        <a
                                            href="{{ route('admin.orders.show', $order) }}"
                                            class="rounded-md bg-secondary px-2.5 py-1 text-xs font-medium transition-colors hover:bg-secondary/70"
                                        >
                                            Detail
                                        </a>
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
        {{ $orders->links() }}
    </div>

</x-layouts.admin>
