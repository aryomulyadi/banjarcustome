<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Nota Pesanan — Banjar Custome</title>
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
        .actions { max-width: 860px; margin: 16px auto 0; display: flex; gap: 8px; }
        .actions button, .actions a { font: inherit; font-size: 14px; padding: 8px 16px; border: 1px solid #1c1917; background: #fff; color: #1c1917; cursor: pointer; text-decoration: none; border-radius: 6px; }
        .actions .primary { background: #1c1917; color: #fff; }
        @media print { .actions { display: none; } body { padding: 0; } .nota { border: 0; } }
    </style>
</head>
<body>
    <div class="nota">
        <div class="head">
            <div>
                <h1>Banjar Custome</h1>
                <p class="muted">{{ config('banjarcustom.address') }}</p>
                <p class="muted">WhatsApp {{ config('banjarcustom.whatsapp') }}</p>
            </div>
            <div style="text-align:right">
                <strong>NOTA PESANAN</strong>
            </div>
        </div>

        <table>
            <tr><th>Tanggal</th><td>{{ $order->created_at->translatedFormat('d F Y, H:i') }} WITA</td></tr>
            <tr><th>Nama Customer</th><td><strong>{{ $order->name }}</strong></td></tr>
            <tr><th>WhatsApp</th><td>{{ $order->whatsapp_number }}</td></tr>
        </table>

        <p class="section-title">Rincian Perhitungan</p>

        @php
            // 1 baris per ukuran (Jumlah = qty ukuran); kolom identik
            // (Jenis/Bahan/Type/Warna) hanya di baris pertama.
            $sizeRows = $order->sizeRows();
            $tableRows = [];

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
            $hasAdjustment = $rowsSubtotal !== null
                && $order->total_price !== null
                && $order->total_price !== $rowsSubtotal;
        @endphp

        @include('admin.orders._nota-table', ['rows' => $tableRows])

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
                <tr class="grand">
                    <th>Total pembayaran</th>
                    <td style="text-align:right"><strong>Rp {{ number_format($order->grandTotalPrice(), 0, ',', '.') }}</strong></td>
                </tr>
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
            <span>Dokumen ini dicetak dari sistem Banjar Custome.</span>
            @if ($order->grandTotalPrice() !== null)
                <span>Skema pembayaran: DP 50% di awal, 50% pelunasan sebelum diambil/dikirim.</span>
            @else
                <span>Harga &amp; pembayaran menyusul konfirmasi CS. Skema: DP 50% di awal, 50% pelunasan sebelum diambil/dikirim.</span>
            @endif
        </div>
    </div>

    <div class="actions">
        <button type="button" class="primary" id="btn-cetak">Cetak</button>
        <a href="{{ route('admin.orders.show', $order) }}">Kembali</a>
    </div>

    <script nonce="{{ request()->attributes->get('csp_nonce') }}">
        document.getElementById('btn-cetak').addEventListener('click', function () { window.print(); });
    </script>
</body>
</html>
