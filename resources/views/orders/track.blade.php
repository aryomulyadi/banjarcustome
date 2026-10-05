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
                <div class="mb-5 rounded-md border border-destructive/30 bg-destructive/10 px-4 py-3 text-sm font-medium text-destructive">
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
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">WhatsApp</span>
                        <span class="font-medium">{{ $order->whatsapp_number }}</span>
                    </div>
                </div>

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
