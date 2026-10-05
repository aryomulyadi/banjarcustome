<x-layouts.admin :title="'Detail Pesanan #'.$order->id">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.orders.index') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-muted-foreground transition-colors hover:text-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Kembali ke daftar pesanan
        </a>
        <a
            href="{{ route('admin.orders.print', $order) }}"
            target="_blank"
            rel="noopener noreferrer"
            class="inline-flex h-9 items-center gap-2 rounded-md border border-border px-3 text-sm font-medium transition-colors hover:bg-secondary"
        >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect width="12" height="8" x="6" y="14"/></svg>
            Cetak Nota
        </a>
    </div>

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
                        <dt class="text-muted-foreground">Jenis pengerjaan</dt>
                        <dd class="text-right font-medium">{{ $order->service_type ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-border pb-3">
                        <dt class="text-muted-foreground">Deadline</dt>
                        <dd class="text-right font-medium">{{ $order->deadline?->translatedFormat('d F Y') ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-border pb-3">
                        <dt class="text-muted-foreground">Estimasi</dt>
                        <dd class="text-right font-medium">
                            @if ($order->is_express)
                                <span class="rounded-full bg-destructive/15 px-2 py-0.5 text-xs font-semibold uppercase tracking-wide text-destructive">Express</span>
                                <span class="text-xs font-normal text-muted-foreground">same-day / &lt;10 hari</span>
                            @else
                                Reguler (3–7 hari kaos, 10–12 hari jersey)
                            @endif
                        </dd>
                    </div>
                    <div class="flex justify-between gap-4 border-b border-border pb-3">
                        <dt class="text-muted-foreground">Pengiriman</dt>
                        <dd class="text-right font-medium">
                            {{ $order->delivery_method === 'ambil' ? 'Ambil sendiri' : ($order->delivery_method === 'kirim' ? 'Dikirim' : '—') }}
                        </dd>
                    </div>
                    @if ($order->address)
                        <div class="flex justify-between gap-4 border-b border-border pb-3">
                            <dt class="text-muted-foreground">Alamat</dt>
                            <dd class="max-w-[60%] text-right font-medium">{{ $order->address }}</dd>
                        </div>
                    @endif
                    @if ($order->size_quantities)
                        <div class="flex justify-between gap-4 border-b border-border pb-3">
                            <dt class="text-muted-foreground">Per ukuran</dt>
                            <dd class="text-right font-medium">
                                @foreach ($order->size_quantities as $size => $qty)
                                    {{ $size }}: {{ $qty }}{{ $loop->last ? '' : ', ' }}
                                @endforeach
                            </dd>
                        </div>
                    @endif
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
                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">
                    {{ $order->order_details ?? '—' }}
                </p>
                @if ($order->notes)
                    <h3 class="mt-4 text-sm font-semibold">Catatan Tambahan</h3>
                    <p class="mt-1.5 whitespace-pre-line text-sm leading-relaxed text-muted-foreground">{{ $order->notes }}</p>
                @endif
            </div>

            <div class="rounded-xl border border-border bg-card p-5">
                <h2 class="text-base font-semibold">Riwayat Status</h2>
                <ol class="mt-3 space-y-3">
                    @forelse ($order->statusHistory as $history)
                        <li class="flex items-start justify-between gap-4 text-sm">
                            <span>
                                @if ($history->from_status)
                                    {{ $history->fromLabel() }} → {{ $history->toLabel() }}
                                @else
                                    Pesanan dibuat ({{ $history->toLabel() }})
                                @endif
                                @if ($history->changer)
                                    <span class="block text-xs text-muted-foreground">oleh {{ $history->changer->name }}</span>
                                @endif
                            </span>
                            <span class="shrink-0 text-xs text-muted-foreground">{{ $history->created_at->translatedFormat('d M Y, H:i') }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-muted-foreground">Belum ada riwayat.</li>
                    @endforelse
                </ol>
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
                        aria-label="Status pesanan"
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
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
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
