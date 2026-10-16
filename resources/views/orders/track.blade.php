<x-layouts.app
    :title="'Cek Status Pesanan - Banjar Custome'"
    :description="'Lacak status pesanan konveksi dan sablon Anda di Banjar Custome menggunakan nomor order dan nomor WhatsApp.'"
    :noindex="true"
>

    <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-primary/15 text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
            </span>
            <h1 class="mt-4 text-2xl font-bold sm:text-3xl">Cek Status Pesanan</h1>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                Masukkan nomor order dan nomor WhatsApp yang Anda gunakan saat memesan.
            </p>
        </div>

        <div class="mt-8 rounded-2xl border border-border bg-card p-6 shadow-sm sm:p-8">
            @if ($errors->any())
                <div class="mb-5 rounded-md border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm font-medium text-destructive" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('track.store') }}" class="grid gap-4 sm:grid-cols-2">
                @csrf

                <div class="space-y-2">
                    <x-label for="order_id">Nomor Order</x-label>
                    <x-input
                        id="order_id"
                        name="order_id"
                        type="number"
                        min="1"
                        value="{{ old('order_id') }}"
                        placeholder="cth. 12"
                        required
                    />
                </div>

                <div class="space-y-2">
                    <x-label for="whatsapp_number">Nomor WhatsApp</x-label>
                    <x-input
                        id="whatsapp_number"
                        name="whatsapp_number"
                        value="{{ old('whatsapp_number') }}"
                        placeholder="0812xxxxxxx"
                        required
                    />
                </div>

                <div class="sm:col-span-2">
                    <x-button type="submit">Cek Status</x-button>
                </div>
            </form>
        </div>

        @if (session('track_flash'))
            <div class="mt-6 rounded-md border border-primary/30 bg-primary/10 px-4 py-3 text-sm font-medium text-primary" role="status">
                {{ session('track_flash') }}
            </div>
        @endif

        @if (session('track_error'))
            <div class="mt-6 rounded-md border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm font-medium text-destructive" role="alert">
                {{ session('track_error') }}
            </div>
        @endif

        @if ($errors->any() && $order)
            <div class="mt-6 rounded-md border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm font-medium text-destructive" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        @if ($order)
            <div class="mt-6 rounded-2xl border border-primary/30 bg-card p-6 shadow-sm sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-lg font-bold">
                        Pesanan <span class="text-primary">#{{ $order->id }}</span>
                    </h2>
                    <span class="rounded-full bg-primary/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-primary">
                        {{ $order->statusLabel() }}
                    </span>
                </div>

                <div class="mt-5 space-y-3 rounded-xl border border-border bg-muted/50 p-5 text-sm">
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">Produk</span>
                        <span class="text-right font-medium">{{ $order->product?->title ?? 'Custom' }}</span>
                    </div>
                    @if ($order->quantity)
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Jumlah</span>
                            <span class="font-medium">{{ $order->quantity }} pcs</span>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">Tanggal pesan</span>
                        <span class="font-medium">{{ $order->created_at->translatedFormat('d M Y') }}</span>
                    </div>
                    @if ($order->deadline)
                        <div class="flex justify-between gap-4">
                            <span class="text-muted-foreground">Deadline</span>
                            <span class="font-medium">
                                {{ $order->deadline->translatedFormat('d M Y') }}
                                @if ($order->isOverdue())
                                    <span class="ml-1 rounded-full bg-destructive/15 px-2 py-0.5 text-[10px] font-semibold uppercase text-destructive">Lewat</span>
                                @endif
                            </span>
                        </div>
                    @endif
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">WhatsApp</span>
                        <span class="font-medium">{{ $order->whatsapp_number }}</span>
                    </div>
                </div>

                @php $history = $order->statusHistory()->latest('id')->get(); @endphp
                <div class="mt-6">
                    <h3 class="text-sm font-semibold">Riwayat Pesanan</h3>
                    <ol class="mt-3 space-y-3">
                        @forelse ($history as $entry)
                            <li class="flex items-start justify-between gap-4 text-sm">
                                <span>
                                    @if ($entry->from_status)
                                        {{ $entry->fromLabel() }} → {{ $entry->toLabel() }}
                                    @else
                                        Pesanan dibuat ({{ $entry->toLabel() }})
                                    @endif
                                </span>
                                <span class="shrink-0 text-xs text-muted-foreground">{{ $entry->created_at->translatedFormat('d M Y, H:i') }}</span>
                            </li>
                        @empty
                            <li class="text-sm text-muted-foreground">Belum ada riwayat.</li>
                        @endforelse
                    </ol>
                </div>

                @if ($order->design_file_admin)
                    <div class="mt-6 rounded-xl border border-border p-5">
                        <h3 class="text-sm font-semibold">Desain</h3>
                        @if ($order->design_approved_at)
                            <p class="mt-1 text-xs text-muted-foreground">Desain sudah Anda setujui {{ $order->design_approved_at->translatedFormat('d M Y, H:i') }}.</p>
                        @else
                            <p class="mt-1 text-xs text-muted-foreground">Hasil desain sudah siap — silakan periksa, lalu setujui atau minta revisi.</p>
                        @endif
                        <a
                            href="{{ route('track.design', ['token' => $order->tracking_token]) }}"
                            class="mt-3 inline-flex h-10 w-full items-center justify-center gap-2 rounded-md border border-border text-sm font-medium transition-colors hover:bg-secondary"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                            Lihat / Unduh Desain
                        </a>
                    </div>

                    @if ($order->design_approved_at === null && in_array($order->status, [\App\Models\CustomOrder::STATUS_DESIGN, \App\Models\CustomOrder::STATUS_READY], true))
                        <div class="mt-4 rounded-xl border border-border p-5">
                            <h3 class="text-sm font-semibold">Persetujuan Desain</h3>
                            <form method="POST" action="{{ route('track.approve', ['token' => $order->tracking_token]) }}" class="mt-3">
                                @csrf
                                <x-button type="submit" class="w-full">Setujui Desain</x-button>
                            </form>

                            <details class="mt-4">
                                <summary class="cursor-pointer text-sm font-medium text-muted-foreground hover:text-foreground">Minta revisi (gratis)</summary>
                                <form method="POST" action="{{ route('track.revision', ['token' => $order->tracking_token]) }}" class="mt-3 space-y-3">
                                    @csrf
                                    <div class="space-y-1.5">
                                        <x-label for="revision_reason">Yang perlu diubah</x-label>
                                        <textarea
                                            id="revision_reason"
                                            name="revision_reason"
                                            rows="3"
                                            maxlength="1000"
                                            required
                                            placeholder="cth. Logo diperbesar, warna biru diganti navy."
                                            class="flex w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring"
                                        >{{ old('revision_reason') }}</textarea>
                                        @error('revision_reason')
                                            <p class="text-xs text-destructive" role="alert">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <x-button type="submit" variant="secondary" class="w-full">Kirim Permintaan Revisi</x-button>
                                </form>
                            </details>
                        </div>
                    @endif
                @elseif (in_array($order->status, [\App\Models\CustomOrder::STATUS_DESIGN, \App\Models\CustomOrder::STATUS_READY], true))
                    <div class="mt-6 rounded-xl border border-border p-5">
                        <h3 class="text-sm font-semibold">Desain</h3>
                        <p class="mt-1 text-xs text-muted-foreground">Desain sedang disiapkan — akan muncul di halaman ini beserta tombol persetujuan.</p>
                    </div>
                @endif

                <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                    <a
                        href="{{ $order->whatsappLink() }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex h-11 flex-1 items-center justify-center gap-2 rounded-md bg-primary px-6 text-sm font-bold text-primary-foreground transition-colors hover:bg-primary/90"
                    >
                        Tanya via WhatsApp
                    </a>
                    <a
                        href="{{ route('track.create') }}"
                        class="inline-flex h-11 flex-1 items-center justify-center rounded-md border border-border px-6 text-sm font-semibold transition-colors hover:bg-secondary"
                    >
                        Cek Pesanan Lain
                    </a>
                </div>
            </div>
        @endif
    </div>

</x-layouts.app>
