<x-layouts.admin :title="'Antrian Produksi'">

    <div class="flex flex-wrap items-center gap-2">
        <a
            href="{{ route('admin.produksi') }}"
            class="rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ $activeStatus === '' && ! $mine ? 'bg-primary text-primary-foreground' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
        >
            Semua <span class="opacity-70">({{ $counts[App\Models\CustomOrder::STATUS_READY] + $counts[App\Models\CustomOrder::STATUS_PRODUCTION] }})</span>
        </a>
        <a
            href="{{ route('admin.produksi', array_filter(['status' => App\Models\CustomOrder::STATUS_READY])) }}"
            class="rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ $activeStatus === App\Models\CustomOrder::STATUS_READY && ! $mine ? 'bg-primary text-primary-foreground' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
        >
            Siap Produksi <span class="opacity-70">({{ $counts[App\Models\CustomOrder::STATUS_READY] }})</span>
        </a>
        <a
            href="{{ route('admin.produksi', array_filter(['status' => App\Models\CustomOrder::STATUS_PRODUCTION])) }}"
            class="rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ $activeStatus === App\Models\CustomOrder::STATUS_PRODUCTION && ! $mine ? 'bg-primary text-primary-foreground' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
        >
            Sedang Diproses <span class="opacity-70">({{ $counts[App\Models\CustomOrder::STATUS_PRODUCTION] }})</span>
        </a>
        <a
            href="{{ route('admin.produksi', array_filter(['status' => $activeStatus ?: null, 'milik' => 1])) }}"
            class="rounded-full px-3.5 py-1.5 text-sm font-medium transition-colors {{ $mine ? 'bg-primary text-primary-foreground' : 'border border-border text-muted-foreground hover:bg-secondary' }}"
        >
            Milik Saya
        </a>
    </div>

    <p class="mt-3 text-xs text-muted-foreground">
        Hanya pesanan berstatus Siap Produksi dan Sedang Diproses. Filter "Milik Saya" menampilkan pesanan
        yang operatornya sama dengan nama akun Anda.
    </p>

    <div class="mt-5 grid gap-4 lg:grid-cols-2">
        @forelse ($orders as $order)
            <div class="flex flex-col rounded-xl border border-border bg-card p-5">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-primary hover:underline">
                        #{{ $order->id }}
                    </a>
                    <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold text-primary">
                        {{ $order->statusLabel() }}
                    </span>
                    @if ($order->is_express)
                        <span class="rounded-full bg-destructive/15 px-2 py-0.5 text-[10px] font-semibold uppercase text-destructive">Express</span>
                    @endif
                    @if ($order->isOverdue())
                        <span class="rounded-full bg-destructive/15 px-2 py-0.5 text-[10px] font-semibold uppercase text-destructive">Lewat deadline</span>
                    @endif
                    @if ($order->assigned_operator && $order->assigned_operator === auth()->user()?->name)
                        <span class="rounded-full bg-secondary px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-foreground">Milik saya</span>
                    @endif
                </div>

                <p class="mt-3 font-medium">{{ $order->name }}</p>
                <p class="text-xs text-muted-foreground">{{ $order->whatsapp_number }}</p>

                <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-1.5 text-sm">
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted-foreground">Produk</dt>
                        <dd class="text-right font-medium">{{ $order->product?->title ?? 'Custom' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted-foreground">Qty</dt>
                        <dd class="text-right font-medium">{{ $order->quantity ? $order->quantity.' pcs' : '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted-foreground">Jenis</dt>
                        <dd class="text-right font-medium">{{ $order->service_type ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-2">
                        <dt class="text-muted-foreground">Deadline</dt>
                        <dd class="text-right font-medium">{{ $order->deadline?->translatedFormat('d M Y') ?? '—' }}</dd>
                    </div>
                    <div class="col-span-2 flex justify-between gap-2">
                        <dt class="text-muted-foreground">Operator</dt>
                        <dd class="text-right font-medium">{{ $order->assigned_operator ?? 'Belum ditentukan' }}</dd>
                    </div>
                </dl>

                <form method="POST" action="{{ route('admin.orders.progress', $order) }}" class="mt-4 space-y-2">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="redirect" value="queue">

                    @foreach (\App\Models\CustomOrder::PROGRESS_FLAGS as $flag => $label)
                        <label class="flex items-center justify-between gap-3 rounded-md border border-border px-3 py-2 text-sm">
                            <span>{{ $label }}</span>
                            <span class="flex items-center gap-2">
                                <span class="text-xs {{ $order->{$flag} ? 'text-primary' : 'text-muted-foreground' }}">{{ $order->{$flag} ? 'ok' : 'nok' }}</span>
                                <input
                                    type="checkbox"
                                    name="{{ $flag }}"
                                    value="1"
                                    {{ $order->{$flag} ? 'checked' : '' }}
                                    class="h-4 w-4 rounded border-input"
                                    aria-label="{{ $label }} ok"
                                >
                            </span>
                        </label>
                    @endforeach

                    <x-button type="submit" class="w-full" variant="secondary">Simpan Progres</x-button>
                </form>

                <div class="mt-3 flex flex-wrap gap-2 text-sm">
                    <a
                        href="{{ route('admin.orders.show', $order) }}"
                        class="inline-flex h-9 flex-1 items-center justify-center rounded-md bg-secondary px-3 font-medium transition-colors hover:bg-secondary/70"
                    >
                        Lihat Detail
                    </a>
                    <a
                        href="{{ route('admin.orders.card', $order) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-border px-3 font-medium transition-colors hover:bg-secondary"
                    >
                        Kartu Kerja
                    </a>
                    <a
                        href="{{ route('admin.orders.spk', $order) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-9 items-center justify-center rounded-md border border-border px-3 font-medium transition-colors hover:bg-secondary"
                    >
                        SPK
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-xl border border-border bg-card px-5 py-10 text-center lg:col-span-2">
                <p class="text-sm text-muted-foreground">Tidak ada pesanan dalam antrian produksi.</p>
            </div>
        @endforelse
    </div>

</x-layouts.admin>
