<x-layouts.admin :title="'Detail Pesanan #'.$order->id">

    <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
        Kembali ke daftar pesanan
    </a>

    <div class="mt-4 grid gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            <div class="rounded-xl border border-border bg-card p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-base font-semibold">Informasi Pesanan</h2>
                    <span class="rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-primary">
                        {{ $order->statusLabel() }}
                    </span>
                </div>

                <dl class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4 border-b border-border pb-3">
                        <dt class="text-muted-foreground">Pelanggan</dt>
                        <dd class="text-right font-medium">{{ $order->name }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-border pb-3">
                        <dt class="text-muted-foreground">WhatsApp</dt>
                        <dd class="text-right font-medium">{{ $order->whatsapp_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-border pb-3">
                        <dt class="text-muted-foreground">Produk</dt>
                        <dd class="text-right font-medium">{{ $order->product?->title ?? 'Custom' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-border pb-3">
                        <dt class="text-muted-foreground">Jumlah</dt>
                        <dd class="text-right font-medium">{{ $order->quantity ? $order->quantity.' pcs' : '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-border pb-3">
                        <dt class="text-muted-foreground">Tanggal masuk</dt>
                        <dd class="text-right font-medium">{{ $order->created_at->translatedFormat('d M Y, H:i') }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-muted-foreground">Terakhir diperbarui</dt>
                        <dd class="text-right font-medium">{{ $order->updated_at->translatedFormat('d M Y, H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-xl border border-border bg-card p-5">
                <h2 class="text-base font-semibold">Detail Pesanan</h2>
                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{{ $order->order_details }}</p>
            </div>
        </div>

        <div class="space-y-5">
            <div class="rounded-xl border border-border bg-card p-5">
                <h2 class="text-base font-semibold">Perbarui Status</h2>

                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="mt-4 space-y-4">
                    @csrf
                    @method('PATCH')

                    <select
                        name="status"
                        class="flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                    >
                        @foreach (\App\Models\CustomOrder::STATUS_LABELS as $key => $label)
                            <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>

                    <x-button type="submit" class="w-full">Simpan Status</x-button>
                </form>
            </div>

            <div class="rounded-xl border border-border bg-card p-5">
                <h2 class="text-base font-semibold">File Desain</h2>

                @if ($order->design_file)
                    <p class="mt-2 text-sm text-muted-foreground">Pelanggan mengunggah file desain.</p>
                    <a
                        href="{{ route('admin.orders.design', $order) }}"
                        class="mt-4 inline-flex h-10 w-full items-center justify-center gap-2 rounded-md border border-border text-sm font-medium transition-colors hover:bg-secondary"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                        Unduh File
                    </a>
                @else
                    <p class="mt-2 text-sm text-muted-foreground">Tidak ada file desain yang diunggah.</p>
                @endif
            </div>

            <a
                href="{{ $order->customerWhatsappLink() }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex h-11 w-full items-center justify-center gap-2 rounded-md bg-primary text-sm font-bold text-primary-foreground transition-colors hover:bg-primary/90"
            >
                Chat Pelanggan via WhatsApp
            </a>
        </div>
    </div>

</x-layouts.admin>
