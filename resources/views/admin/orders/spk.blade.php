<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>SPK Produksi - Banjar Custome</title>
    @include('partials.favicon')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page { size: A4 landscape; margin: 0; }
        html, body { background: #f4f4f5; }
        @media print {
            html, body { background: #fff; }
            .actions { display: none !important; }
            .sheet { width: auto; min-height: auto; margin: 0; box-shadow: none; border: 0; }
        }
    </style>
</head>
<body class="min-h-screen antialiased">
    @php
        $rows = $order->sizeRows() ?? [];
        $totalPcs = (int) ($order->quantity ?: array_sum(array_column($rows, 'qty')));
        $isJersey = $order->isJersey();
        $designExt = strtolower(pathinfo((string) $order->design_file_admin, PATHINFO_EXTENSION));
        $designIsImage = $order->design_file_admin !== null && in_array($designExt, ['jpg', 'jpeg', 'png', 'webp'], true);
    @endphp

    <div class="actions mx-auto flex max-w-[297mm] gap-2 px-4 pt-4 print:hidden">
        <button type="button" id="btn-print" class="rounded-md bg-foreground px-4 py-2 text-sm font-semibold text-background hover:opacity-90">Cetak</button>
        <a href="{{ route('admin.orders.show', $order) }}" class="rounded-md border border-border bg-card px-4 py-2 text-sm font-semibold hover:bg-secondary">Kembali ke Detail</a>
    </div>

    <main class="sheet mx-auto my-4 flex w-[297mm] min-h-[210mm] flex-col border border-border bg-white p-6 shadow-sm sm:p-7">
        {{-- Strip atas --}}
        <header class="flex items-center justify-between gap-4 border-b-2 border-foreground pb-4">
            <div>
                <p class="text-2xl font-black tracking-tight sm:text-3xl">BANJAR CUSTOME</p>
                <p class="mt-0.5 text-[11px] font-medium uppercase tracking-[0.2em] text-muted-foreground">Konveksi &amp; Sablon - Banjarmasin</p>
            </div>
            <span class="rounded-full bg-foreground px-4 py-1.5 text-xs font-bold uppercase tracking-[0.18em] text-background">SPK Produksi</span>
        </header>

        {{-- Identitas pesanan --}}
        <section class="mt-5 grid gap-4 sm:grid-cols-[1fr_auto]">
            <div class="rounded-xl bg-blue-600 px-5 py-4 text-white">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-blue-100">Jenis Pengerjaan</p>
                <p class="mt-1 text-2xl font-black leading-tight sm:text-3xl">{{ $order->service_type ?? 'Custom' }}</p>
                <p class="mt-2 text-sm font-semibold text-blue-100">Pelanggan: {{ $order->name }}</p>
            </div>
            <div class="rounded-xl bg-red-600 px-5 py-4 text-white sm:min-w-[240px]">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-red-100">Deadline Produksi</p>
                <p class="mt-1 text-2xl font-black leading-tight sm:text-3xl">{{ $order->deadline?->translatedFormat('d F Y') ?? 'Menyesuaikan' }}</p>
                @if ($order->is_express)
                    <span class="mt-2 inline-block rounded-full bg-white px-3 py-1 text-xs font-black uppercase tracking-wider text-red-600">Express</span>
                @endif
            </div>
        </section>

        {{-- Tengah: desain + rincian ukuran --}}
        <section class="mt-4 grid flex-1 gap-4 sm:grid-cols-2">
            <div class="flex flex-col rounded-xl border-2 border-dashed border-border p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-muted-foreground">Desain Final</p>
                <div class="mt-3 flex flex-1 items-center justify-center overflow-hidden rounded-lg bg-secondary/40">
                    @if ($designIsImage)
                        <img src="{{ route('admin.orders.design.preview', $order) }}" alt="Desain pesanan #{{ $order->id }}" class="max-h-[110mm] w-full object-contain">
                    @elseif ($order->design_file_admin)
                        <p class="px-4 text-center text-sm text-muted-foreground">File desain: <span class="font-semibold text-foreground">{{ basename($order->design_file_admin) }}</span> (PDF/arsip - buka di detail pesanan)</p>
                    @else
                        <p class="px-4 text-center text-sm text-muted-foreground">Belum ada file desain final - cek detail pesanan.</p>
                    @endif
                </div>
            </div>

            <div class="flex flex-col rounded-xl border border-border bg-card p-4">
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-muted-foreground">Rincian Ukuran &amp; Total Keseluruhan</p>

                @if ($rows !== [])
                    <table class="mt-3 w-full border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-border text-left text-[11px] uppercase tracking-wider text-muted-foreground">
                                <th class="py-1.5 font-semibold">Ukuran</th>
                                <th class="py-1.5 text-right font-semibold">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @foreach ($rows as $row)
                                <tr>
                                    <td class="py-1.5 font-semibold">{{ $row['size'] }}</td>
                                    <td class="py-1.5 text-right font-medium tabular-nums">{{ $row['qty'] }} pcs</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="mt-3 text-sm text-muted-foreground">Tanpa rincian ukuran per baris.</p>
                @endif

                <div class="mt-auto flex items-end justify-between gap-3 border-t-2 border-foreground pt-4">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-muted-foreground">Total Keseluruhan</p>
                        <p class="text-xs text-muted-foreground">Seluruh ukuran dalam pesanan ini</p>
                    </div>
                    <p class="text-5xl font-black leading-none text-red-600 tabular-nums">{{ $totalPcs }}<span class="ml-1 text-lg font-bold text-red-600/80">pcs</span></p>
                </div>
            </div>
        </section>

        {{-- Spesifikasi --}}
        <section class="mt-4">
            <div class="grid grid-cols-2 border-2 border-foreground {{ $isJersey ? 'sm:grid-cols-4' : 'sm:grid-cols-3' }}">
                <div class="border-r-2 border-foreground px-4 py-3 last:border-r-0">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground">Jenis</p>
                    <p class="mt-1 text-sm font-bold">{{ $order->service_type ?? 'Custom' }}</p>
                </div>
                @if ($isJersey)
                    <div class="border-r-2 border-foreground px-4 py-3">
                        <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground">Model Kerah</p>
                        <p class="mt-1 text-sm font-bold">{{ $order->collar_model ?: 'Standar (O-Neck)' }}</p>
                    </div>
                @endif
                <div class="border-r-2 border-foreground px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground">Bahan</p>
                    <p class="mt-1 text-sm font-bold">{{ $order->bahan ?: '-' }}</p>
                </div>
                <div class="px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground">Warna</p>
                    <p class="mt-1 text-sm font-bold">{{ $order->warna ?: '-' }}</p>
                </div>
            </div>

            @if ($order->notes)
                <div class="mt-3 rounded-lg border border-border px-4 py-3">
                    <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-muted-foreground">Catatan</p>
                    <p class="mt-1 whitespace-pre-line text-sm leading-relaxed">{{ $order->notes }}</p>
                </div>
            @endif
        </section>

        <footer class="mt-auto flex items-center justify-between gap-3 border-t border-border pt-3 text-[11px] text-muted-foreground">
            <span>Dokumen kerja internal produksi - Banjar Custome.</span>
            <span>{{ $order->quantity ? 'Total pesanan: '.$order->quantity.' pcs' : '' }}</span>
        </footer>
    </main>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.getElementById('btn-print').addEventListener('click', function () { window.print(); });
    </script>
</body>
</html>
