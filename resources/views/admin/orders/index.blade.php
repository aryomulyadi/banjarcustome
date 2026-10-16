<x-layouts.admin :title="'Pesanan'">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        @php
            $chipQuery = fn (array $overrides): array => array_filter(
                array_merge(['status' => $activeStatus, 'source' => $activeSource, 'q' => $search !== '' ? $search : null], $overrides),
                fn ($value) => $value !== null && $value !== ''
            );
        @endphp
        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('admin.orders.index', $chipQuery(['status' => null])) }}"
                class="rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ ! $activeStatus ? 'bg-primary text-primary-foreground' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
            >
                Semua <span class="opacity-70">({{ $statusCounts['all'] ?? 0 }})</span>
            </a>
            @foreach ($statusLabels as $key => $label)
                <a
                    href="{{ route('admin.orders.index', $chipQuery(['status' => $key])) }}"
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
            @if ($activeSource)
                <input type="hidden" name="source" value="{{ $activeSource }}">
            @endif
            <x-input name="q" value="{{ $search }}" placeholder="Cari nama / no. WA…" class="w-full lg:w-64" aria-label="Cari pesanan" />
            <x-button type="submit" variant="secondary">Cari</x-button>
            <a
                href="{{ route('admin.orders.import') }}"
                class="inline-flex h-10 items-center justify-center rounded-md border border-border px-4 text-sm font-medium transition-colors hover:bg-secondary"
            >
                Impor
            </a>
            <a
                href="{{ route('admin.orders.export', $chipQuery([])) }}"
                class="inline-flex h-10 items-center justify-center rounded-md border border-border px-4 text-sm font-medium transition-colors hover:bg-secondary"
            >
                Export Excel
            </a>
        </form>
    </div>

    <div class="mt-3 flex flex-wrap gap-2 text-sm">
        @foreach (['' => 'Semua Sumber', \App\Models\CustomOrder::SOURCE_FORM => 'Formulir', \App\Models\CustomOrder::SOURCE_IMPORT => 'Impor'] as $sourceKey => $sourceLabel)
            <a
                href="{{ route('admin.orders.index', $chipQuery(['source' => $sourceKey ?: null])) }}"
                class="rounded-full px-3 py-1 font-medium transition-colors {{ ($activeSource ?? '') === $sourceKey ? 'bg-secondary text-foreground' : 'text-muted-foreground hover:bg-secondary/60' }}"
            >
                {{ $sourceLabel }}
            </a>
        @endforeach
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
                            <th class="px-5 py-3 font-medium">Progres</th>
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
                                <td class="px-5 py-3">
                                    {{ $order->product?->title ?? 'Custom' }}
                                    @if ($order->is_express)
                                        <span class="ml-1 inline-block rounded-full bg-destructive/15 px-2 py-0.5 text-[10px] font-semibold uppercase text-destructive">Express</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">{{ $order->quantity ?? '—' }}</td>
                                <td class="px-5 py-3">
                                    <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary">{{ $order->statusLabel() }}</span>
                                    @if ($order->source === \App\Models\CustomOrder::SOURCE_IMPORT)
                                        <span class="mt-1 block w-fit rounded-full bg-secondary px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-muted-foreground">Impor</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    <span class="flex items-center gap-1" title="Pesan Bahan / Produksi / Finish">
                                        @foreach (\App\Models\CustomOrder::PROGRESS_FLAGS as $flag => $label)
                                            <span class="inline-flex h-5 w-5 items-center justify-center rounded-full text-[10px] font-bold {{ $order->{$flag} ? 'bg-primary/15 text-primary' : 'bg-secondary text-muted-foreground' }}">{{ $order->{$flag} ? '✓' : '·' }}</span>
                                        @endforeach
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-muted-foreground">
                                    <span class="block">{{ $order->created_at->translatedFormat('d M Y') }}</span>
                                    @if ($order->deadline)
                                        <span class="block text-xs">Deadline: {{ $order->deadline->translatedFormat('d M Y') }}</span>
                                    @endif
                                    @if ($order->isOverdue())
                                        <span class="mt-1 block w-fit rounded-full bg-destructive/15 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-destructive">Lewat deadline</span>
                                    @endif
                                </td>                                <td class="px-5 py-3">
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
