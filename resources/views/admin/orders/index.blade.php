<x-layouts.admin :title="'Pesanan'">

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        @php
            $chipQuery = fn (array $overrides): array => array_filter(
                array_merge(
                    [
                        'status' => $activeStatus,
                        'source' => $activeSource,
                        'q' => $search !== '' ? $search : null,
                        'bulan' => $activeBulan,
                        'jenis' => $activeJenis !== [] ? $activeJenis : null,
                    ],
                    $overrides
                ),
                fn ($value) => $value !== null && $value !== '' && $value !== []
            );
        @endphp
        <div class="flex flex-wrap gap-2">
            <a
                href="{{ route('admin.orders.index', $chipQuery(['status' => null])) }}"
                class="rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ ! $activeStatus ? 'bg-primary text-primary-foreground' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
            >
                Semua <span>({{ $statusCounts['all'] ?? 0 }})</span>
            </a>
            @foreach ($statusLabels as $key => $label)
                <a
                    href="{{ route('admin.orders.index', $chipQuery(['status' => $key])) }}"
                    class="rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ $activeStatus === $key ? \App\Support\StatusTheme::classes($key).' border border-transparent' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
                >
                    {{ $label }} <span>({{ $statusCounts[$key] ?? 0 }})</span>
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex flex-wrap items-end gap-2">
            @if ($activeStatus)
                <input type="hidden" name="status" value="{{ $activeStatus }}">
            @endif
            @if ($activeSource)
                <input type="hidden" name="source" value="{{ $activeSource }}">
            @endif
            <div class="w-full sm:w-40">
                <x-label for="bulan">Bulan</x-label>
                <x-select id="bulan" name="bulan" @change="$el.form.submit()">
                    <option value="">Semua bulan</option>
                    @foreach ($bulanOptions as $value)
                        <option value="{{ $value }}" @selected($activeBulan === $value)>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $value)->translatedFormat('F Y') }}</option>
                    @endforeach
                </x-select>
            </div>
            <div class="relative w-full sm:w-56" x-data="{ jenisOpen: false }" @click.outside="jenisOpen = false">
                <x-label>Jenis</x-label>
                <button
                    type="button"
                    aria-haspopup="true"
                    :aria-expanded="jenisOpen"
                    class="flex h-10 w-full items-center justify-between gap-2 rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm transition-colors hover:bg-secondary/50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    @click="jenisOpen = !jenisOpen"
                >
                    <span class="truncate">
                        Jenis
                        @if ($activeJenis !== [])
                            <span class="ml-1 inline-flex h-5 min-w-5 items-center justify-center rounded-full bg-primary px-1.5 text-[11px] font-bold text-primary-foreground">{{ count($activeJenis) }}</span>
                        @endif
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true" class="h-4 w-4 shrink-0 text-muted-foreground transition-transform" :class="jenisOpen && 'rotate-180'">
                        <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                    </svg>
                </button>
                <div x-show="jenisOpen" x-cloak x-transition.origin.top.left class="absolute left-0 z-30 mt-1 w-72 rounded-md border border-border bg-popover p-2 shadow-lg">
                    <div class="max-h-64 space-y-0.5 overflow-y-auto">
                        @foreach ($jenisCounts as $value => $total)
                            <label class="flex cursor-pointer items-center gap-2.5 rounded-md px-2 py-1.5 hover:bg-secondary">
                                <input type="checkbox" name="jenis[]" value="{{ $value }}" @checked(in_array($value, $activeJenis, true)) class="h-4 w-4 rounded border-input accent-primary" />
                                <span class="text-sm">{{ $value === 'kosong' ? 'Tanpa jenis' : $value }}</span>
                                <span class="ml-auto text-xs tabular-nums text-muted-foreground">({{ $total }})</span>
                            </label>
                        @endforeach
                    </div>
                    <div class="mt-2 flex items-center gap-2 border-t border-border pt-2">
                        <x-button type="submit" class="flex-1">Terapkan</x-button>
                        <a
                            href="{{ route('admin.orders.index', $chipQuery(['jenis' => null])) }}"
                            class="inline-flex h-10 items-center justify-center rounded-md border border-border px-3 text-xs font-medium transition-colors hover:bg-secondary"
                        >
                            Reset jenis
                        </a>
                    </div>
                </div>
            </div>
            <div class="flex-1 min-w-40">
                <x-label for="q">Pencarian</x-label>
                <x-input id="q" name="q" value="{{ $search }}" placeholder="Cari nama / no. WA…" aria-label="Cari pesanan" />
            </div>
            <div class="flex gap-2">
                <x-button type="submit" variant="secondary">Cari</x-button>
                <a
                    href="{{ route('admin.orders.create') }}"
                    class="inline-flex h-10 items-center justify-center gap-1.5 rounded-md bg-primary px-4 text-sm font-semibold text-primary-foreground transition-colors hover:bg-primary/90"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                    Pesanan Baru
                </a>
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
            </div>
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
        @if ($activeBulan !== null || $activeJenis !== [])
            <span class="ml-auto flex flex-wrap items-center gap-2 text-xs text-muted-foreground">
                Filter aktif:
                @if ($activeBulan !== null)
                    <span class="rounded-full bg-secondary px-2.5 py-0.5 font-medium text-foreground">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $activeBulan)->translatedFormat('F Y') }}</span>
                @endif
                @if ($activeJenis !== [])
                    <span class="rounded-full bg-secondary px-2.5 py-0.5 font-medium text-foreground">
                        {{ collect($activeJenis)->map(fn ($value) => $value === 'kosong' ? 'Tanpa jenis' : $value)->implode(', ') }}
                    </span>
                @endif
                <a href="{{ route('admin.orders.index', $chipQuery(['bulan' => null, 'jenis' => null])) }}" class="font-medium text-primary underline underline-offset-2">Reset</a>
            </span>
        @endif
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
                                    <x-status-chip :status="$order->status" :label="$order->statusLabel()" />
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
                                        <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Hapus pesanan #{{ $order->id }} ({{ $order->name }})?')">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="rounded-md border border-destructive/30 px-2 py-1 text-xs font-medium text-destructive transition-colors hover:bg-destructive/10"
                                                title="Hapus Pesanan"
                                            >
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
        {{ $orders->links() }}
    </div>

</x-layouts.admin>
