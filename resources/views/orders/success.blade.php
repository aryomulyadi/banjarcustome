<x-layouts.app :title="'Pesanan Berhasil Dikirim — Banjar Custome'" :noindex="true">

    <div class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
        <div class="rounded-2xl border border-border bg-card p-6 text-center sm:p-10">
            <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-primary/15 text-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21.801 10A10 10 0 1 1 17 3.335"/><path d="m9 11 3 3L22 4"/></svg>
            </span>

            <h1 class="mt-5 text-2xl font-bold sm:text-3xl">Pesanan Berhasil Dikirim!</h1>
            <p class="mt-2 text-sm leading-relaxed text-muted-foreground">
                Terima kasih, <span class="font-medium text-foreground">{{ $order->name }}</span>. Pesanan Anda
                (<span class="font-medium text-foreground">#{{ $order->id }}</span>) sudah kami terima dan akan segera diproses.
            </p>

            <div class="mx-auto mt-6 max-w-md space-y-3 rounded-xl border border-border bg-muted/50 p-5 text-left text-sm">
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
                @if ($order->size_quantities)
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">Per ukuran</span>
                        <span class="text-right font-medium">
                            @foreach ($order->size_quantities as $size => $qty)
                                {{ $size }}: {{ $qty }}{{ $loop->last ? '' : ', ' }}
                            @endforeach
                        </span>
                    </div>
                @endif
                @if ($order->service_type)
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">Jenis</span>
                        <span class="font-medium">{{ $order->service_type }}</span>
                    </div>
                @endif
                @if ($order->deadline)
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">Deadline</span>
                        <span class="font-medium">{{ $order->deadline->translatedFormat('d F Y') }}</span>
                    </div>
                @endif
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Estimasi</span>
                    <span class="font-medium">
                        @if ($order->is_express)
                            <span class="rounded-full bg-destructive/15 px-2 py-0.5 text-xs font-semibold uppercase text-destructive">Express</span>
                            <span class="text-xs font-normal text-muted-foreground">same-day / &lt;10 hari</span>
                        @else
                            Kaos 3–7 hari, jersey 10–12 hari
                        @endif
                    </span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Pembayaran</span>
                    <span class="font-medium">DP 50% di awal</span>
                </div>
                @if ($order->delivery_method)
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">Pengiriman</span>
                        <span class="font-medium">{{ $order->delivery_method === 'ambil' ? 'Ambil sendiri' : 'Dikirim' }}</span>
                    </div>
                @endif
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">WhatsApp</span>
                    <span class="font-medium">{{ $order->whatsapp_number }}</span>
                </div>
                <div class="flex justify-between gap-4">
                    <span class="text-muted-foreground">Status</span>
                    <span class="rounded-full bg-primary/15 px-2.5 py-0.5 text-xs font-semibold uppercase text-primary">{{ $order->statusLabel() }}</span>
                </div>
                @if ($order->design_file)
                    <div class="flex justify-between gap-4">
                        <span class="text-muted-foreground">File desain</span>
                        <span class="font-medium">Terkirim</span>
                    </div>
                @endif
            </div>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ $order->whatsappLink() }}" target="_blank" rel="noopener noreferrer" class="inline-flex h-12 items-center justify-center gap-2 rounded-md bg-primary px-7 text-sm font-bold text-primary-foreground transition-colors hover:bg-primary/90">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    Kirim Ringkasan ke Admin (WhatsApp)
                </a>
                <a href="{{ route('home') }}" class="inline-flex h-12 items-center justify-center rounded-md border border-border px-7 text-sm font-semibold transition-colors hover:bg-secondary">
                    Kembali ke Beranda
                </a>
            </div>

            <p class="mt-5 text-xs text-muted-foreground">
                Tombol di atas membuka WhatsApp berisi ringkasan pesanan Anda — kirim agar pesanan langsung dikonfirmasi.
                Skema pembayaran: <strong>DP 50%</strong> untuk mulai produksi, 50% sisanya sebelum pesanan diambil/dikirim.
                Simpan nomor order <strong>#{{ $order->id }}</strong> —
                <a href="{{ route('track.create') }}" class="font-medium text-primary underline-offset-4 hover:underline">
                    lacak status pesanan kapan saja
                </a>.
            </p>
        </div>
    </div>

</x-layouts.app>
