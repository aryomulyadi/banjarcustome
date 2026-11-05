<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Nota Pesanan — Banjar Custom</title>
    @include('partials.favicon')
    <style>
        * { box-sizing: border-box; }
        body { font-family: Georgia, 'Times New Roman', serif; color: #1c1917; margin: 0; padding: 32px; background: #fff; }
        .nota { max-width: 860px; margin: 0 auto; border: 1px solid #d6d3d1; padding: 32px; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        .muted { color: #78716c; font-size: 13px; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1c1917; padding-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #e7e5e4; vertical-align: top; }
        th { width: 35%; font-weight: 600; color: #44403c; }
        .section-title { margin-top: 26px; font-size: 13px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #44403c; }
        .nota-table th, .nota-table td { border: 1px solid #d6d3d1; padding: 7px 8px; }
        .nota-table thead th { background: #f5f5f4; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; width: auto; }
        .nota-table .num { text-align: right; white-space: nowrap; }
        .nota-table .wrap { max-width: 130px; }
        .summary { width: 58%; margin-left: auto; margin-top: 18px; }
        .summary th { width: auto; }
        .summary .grand th, .summary .grand td { border-top: 2px solid #1c1917; font-size: 15px; }
        .foot { margin-top: 28px; font-size: 12px; color: #78716c; display: flex; justify-content: space-between; gap: 12px; }
        .actions { max-width: 860px; margin: 16px auto 0; display: flex; flex-wrap: wrap; gap: 8px; }
        .actions button, .actions a { font: inherit; font-size: 14px; padding: 8px 16px; border: 1px solid #1c1917; background: #fff; color: #1c1917; cursor: pointer; text-decoration: none; border-radius: 6px; }
        .actions .primary { background: #1c1917; color: #fff; }
        .actions button:disabled { opacity: .6; cursor: wait; }
        @media (max-width: 640px) {
            body { padding: 12px; }
            .nota { padding: 16px; }
            .head { flex-direction: column; gap: 8px; }
            .head > div:last-child { text-align: left !important; }
            table { font-size: 13px; }
            th, td { padding: 6px 6px; }
            th { width: 38%; }
            .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
            .nota-table { min-width: 560px; }
            .summary { width: 100%; }
            .foot { flex-direction: column; gap: 4px; }
            .actions button, .actions a { flex: 1; min-width: 44%; text-align: center; }
        }
        @media print { .actions { display: none; } body { padding: 0; } .nota { border: 0; } }
    </style>
</head>
<body data-order-id="{{ $order->id }}">
    <div class="nota">
        <div class="head">
            <div>
                <h1>Banjar Custom</h1>
                <p class="muted">{{ config('banjarcustom.address') }}</p>
                <p class="muted">WhatsApp {{ config('banjarcustom.whatsapp') }}</p>
            </div>
            <div style="text-align:right">
                <strong>NOTA PESANAN</strong>
                <p class="muted">#{{ $order->id }}</p>
            </div>
        </div>

        <table>
            <tr><th>Tanggal</th><td>{{ $order->created_at->translatedFormat('d F Y, H:i') }} WITA</td></tr>
            <tr><th>Nama Customer</th><td><strong>{{ $order->name }}</strong></td></tr>
            <tr><th>WhatsApp</th><td>{{ $order->whatsapp_number }}</td></tr>
        </table>

        <p class="section-title">Rincian Perhitungan</p>

        @php
            // Nota multi-spesifikasi: blok `spec_groups` (Jenis/Bahan/Type/Warna per blok).
            // Kolom identik hanya di baris pertama tiap blok — seperti Excel.
            $specGroups = $order->specGroups();
            $tableRows = [];

            if ($specGroups !== []) {
                foreach ($specGroups as $group) {
                    foreach ($group['rows'] as $index => $row) {
                        $first = $index === 0;
                        $unit = $order->groupRowUnit($row['size'], $row['unit']);
                        $tableRows[] = [
                            'jenis' => $first ? ($group['jenis'] !== '' ? $group['jenis'] : ($order->service_type ?? '—')) : '',
                            'bahan' => $first ? ($group['bahan'] !== '' ? $group['bahan'] : ($order->bahan ?? '—')) : '',
                            'type' => $first ? ($group['type'] !== '' ? $group['type'] : ($order->type ?? $order->product?->title ?? 'Custom')) : '',
                            'warna' => $first ? ($group['warna'] !== '' ? $group['warna'] : ($order->warna ?? '—')) : '',
                            'ukuran' => $row['size'] !== '' ? $row['size'] : '—',
                            'jumlah' => $row['qty'].' pcs',
                            'harga' => $unit !== null ? 'Rp '.number_format($unit, 0, ',', '.') : '—',
                            'total' => $unit !== null ? 'Rp '.number_format($unit * $row['qty'], 0, ',', '.') : '—',
                        ];
                    }
                }

                $rowsSubtotal = $order->groupsSubtotal();
            } else {
                $sizeRows = $order->sizeRows();

                if ($sizeRows !== null) {
                    foreach ($sizeRows as $index => $row) {
                        $tableRows[] = [
                            'jenis' => $index === 0 ? ($order->service_type ?? '—') : '',
                            'bahan' => $index === 0 ? ($order->bahan ?? '—') : '',
                            'type' => $index === 0 ? ($order->type ?? $order->product?->title ?? 'Custom') : '',
                            'warna' => $index === 0 ? ($order->warna ?? '—') : '',
                            'ukuran' => $row['size'],
                            'jumlah' => $row['qty'].' pcs',
                            'harga' => $row['unit'] !== null ? 'Rp '.number_format($row['unit'], 0, ',', '.') : '—',
                            'total' => $row['total'] !== null ? 'Rp '.number_format($row['total'], 0, ',', '.') : '—',
                        ];
                    }
                } else {
                    $tableRows[] = [
                        'jenis' => $order->service_type ?? '—',
                        'bahan' => $order->bahan ?? '—',
                        'type' => $order->type ?? $order->product?->title ?? 'Custom',
                        'warna' => $order->warna ?? '—',
                        'ukuran' => '—',
                        'jumlah' => $order->quantity ? $order->quantity.' pcs' : '—',
                        'harga' => $order->unit_price !== null ? 'Rp '.number_format($order->unit_price, 0, ',', '.') : '—',
                        'total' => $order->resolvedTotalPrice() !== null ? 'Rp '.number_format($order->resolvedTotalPrice(), 0, ',', '.') : '—',
                    ];
                }

                $rowsSubtotal = $order->sizeRowsSubtotal();
            }

            $hasAdjustment = $rowsSubtotal !== null
                && $order->total_price !== null
                && $order->total_price !== $rowsSubtotal;
        @endphp

        <div class="table-scroll">
            @include('admin.orders._nota-table', ['rows' => $tableRows])
        </div>

        @if ($order->grandTotalPrice() !== null)
            <table class="summary">
                @if ($hasAdjustment)
                    <tr>
                        <th>Subtotal</th>
                        <td style="text-align:right">Rp {{ number_format($rowsSubtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <th>Penyesuaian manual</th>
                        <td style="text-align:right">{{ $order->total_price >= $rowsSubtotal ? '+' : '−' }} Rp {{ number_format(abs($order->total_price - $rowsSubtotal), 0, ',', '.') }}</td>
                    </tr>
                @else
                    <tr>
                        <th>Subtotal</th>
                        <td style="text-align:right">Rp {{ number_format($order->resolvedTotalPrice(), 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if ($order->expressFeeNumber() !== null)
                    <tr>
                        <th>Biaya express</th>
                        <td style="text-align:right">Rp {{ number_format($order->expressFeeNumber(), 0, ',', '.') }}</td>
                    </tr>
                @endif
                <tr class="{{ $order->dp_amount !== null ? '' : 'grand' }}">
                    <th>Total pembayaran</th>
                    <td style="text-align:right"><strong>Rp {{ number_format($order->grandTotalPrice(), 0, ',', '.') }}</strong></td>
                </tr>
                @if ($order->dp_amount !== null)
                    <tr>
                        <th>DP Diterima</th>
                        <td style="text-align:right">− Rp {{ number_format($order->dp_amount, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="grand">
                        <th>Sisa Pelunasan</th>
                        <td style="text-align:right"><strong>Rp {{ number_format(max(0, $order->grandTotalPrice() - $order->dp_amount), 0, ',', '.') }}</strong></td>
                    </tr>
                @endif
            </table>
        @endif

        <p class="section-title">Detail &amp; Catatan</p>

        <table>
            <tr><th>Detail</th><td>{{ $order->order_details ?? '—' }}</td></tr>
            @if ($order->notes)
                <tr><th>Catatan</th><td>{{ $order->notes }}</td></tr>
            @endif
        </table>

        <div class="foot">
            <span>Dokumen ini dicetak dari sistem Banjar Custom.</span>
            @if ($order->grandTotalPrice() !== null)
                <span>Skema pembayaran: DP 50% di awal, 50% pelunasan sebelum diambil/dikirim.</span>
            @else
                <span>Harga &amp; pembayaran menyusul konfirmasi CS. Skema: DP 50% di awal, 50% pelunasan sebelum diambil/dikirim.</span>
            @endif
        </div>
    </div>

    <div class="actions">
        <button type="button" class="primary" id="btn-cetak">Cetak</button>
        <button type="button" id="btn-pdf">Unduh PDF</button>
        <button type="button" id="btn-jpg">Unduh JPG</button>
        @if ($customerMode ?? false)
            <a href="{{ route('track.token', ['token' => $order->tracking_token]) }}">Kembali</a>
        @else
            <a href="{{ route('admin.orders.show', $order) }}">Kembali</a>
        @endif
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.getElementById('btn-cetak').addEventListener('click', function () { window.print(); });
    </script>
    @vite(['resources/js/nota.js'])
</body>
</html>
