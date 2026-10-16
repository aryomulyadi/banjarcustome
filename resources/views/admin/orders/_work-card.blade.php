<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $docTitle }} — Banjar Custome</title>
    @include('partials.favicon')
    <style>
        * { box-sizing: border-box; }
        body { font-family: Georgia, 'Times New Roman', serif; color: #1c1917; margin: 0; padding: 32px; background: #fff; }
        .sheet { max-width: 860px; margin: 0 auto; border: 1px solid #d6d3d1; padding: 32px; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        h2 { font-size: 16px; margin: 0; }
        .muted { color: #78716c; font-size: 13px; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1c1917; padding-bottom: 16px; }
        .head .doc { text-align: right; }
        .head .doc .meta { margin-top: 6px; font-size: 13px; color: #44403c; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #e7e5e4; vertical-align: top; }
        th { width: 35%; font-weight: 600; color: #44403c; }
        .section-title { margin-top: 26px; font-size: 13px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #44403c; }
        .sizes { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 14px; }
        .sizes th, .sizes td { border: 1px solid #d6d3d1; padding: 7px 8px; }
        .sizes thead th { background: #f5f5f4; font-size: 12px; text-transform: uppercase; letter-spacing: .04em; width: auto; }
        .sizes .num { text-align: right; white-space: nowrap; }
        .design-thumb { margin-top: 12px; }
        .design-thumb img { max-width: 260px; max-height: 260px; border: 1px solid #d6d3d1; display: block; }
        .sign { margin-top: 40px; display: flex; justify-content: space-between; gap: 24px; font-size: 13px; }
        .sign .box { text-align: center; min-width: 180px; }
        .sign .line { margin-top: 56px; border-top: 1px solid #1c1917; padding-top: 4px; }
        .actions { max-width: 860px; margin: 16px auto 0; display: flex; gap: 8px; }
        .actions button, .actions a { font: inherit; font-size: 14px; padding: 8px 16px; border: 1px solid #1c1917; background: #fff; color: #1c1917; cursor: pointer; text-decoration: none; border-radius: 6px; }
        .actions .primary { background: #1c1917; color: #fff; }
        @media print { .actions { display: none; } body { padding: 0; } .sheet { border: 0; } }
    </style>
</head>
<body>
    <div class="sheet">
        <div class="head">
            <div>
                <h1>Banjar Custome</h1>
                <p class="muted">{{ config('banjarcustom.address') }}</p>
                <p class="muted">WhatsApp {{ config('banjarcustom.whatsapp') }}</p>
            </div>
            <div class="doc">
                <h2>{{ $docTitle }}</h2>
                @if (! empty($docNumber))
                    <p class="meta"><strong>{{ $docNumber }}</strong></p>
                @endif
                <p class="meta">Tanggal: {{ $docDate }}</p>
            </div>
        </div>

        <table>
            <tr><th>No. Order</th><td><strong>#{{ $order->id }}</strong></td></tr>
            <tr><th>Customer</th><td>{{ $order->name }}</td></tr>
            <tr><th>WhatsApp</th><td>{{ $order->whatsapp_number }}</td></tr>
            <tr><th>Jenis Pengerjaan</th><td>{{ $order->service_type ?? '—' }}</td></tr>
            <tr><th>Bahan / Type / Warna</th><td>
                {{ $order->bahan ?? '—' }} / {{ $order->type ?? $order->product?->title ?? 'Custom' }} / {{ $order->warna ?? '—' }}
            </td></tr>
            <tr><th>Deadline</th><td>{{ $order->deadline?->translatedFormat('d F Y') ?? '—' }}{{ $order->is_express ? ' (EXPRESS)' : '' }}</td></tr>
            @if (! empty($showOperator))
                <tr><th>Operator / Penanggung Jawab</th><td><strong>{{ $order->assigned_operator ?? '—' }}</strong></td></tr>
            @endif
        </table>

        <p class="section-title">Rincian Ukuran</p>

        @php
            $sizeRows = $order->sizeRows();
            $tableRows = [];

            if ($sizeRows !== null) {
                foreach ($sizeRows as $row) {
                    $tableRows[] = [
                        'ukuran' => $row['size'],
                        'jumlah' => $row['qty'].' pcs',
                        'harga' => $row['unit'] !== null ? 'Rp '.number_format($row['unit'], 0, ',', '.') : '—',
                    ];
                }
            } else {
                $tableRows[] = [
                    'ukuran' => '—',
                    'jumlah' => ($order->quantity ? $order->quantity.' pcs' : '—'),
                    'harga' => '—',
                ];
            }
        @endphp

        <table class="sizes">
            <thead>
                <tr>
                    <th>Ukuran</th>
                    <th class="num">Jumlah</th>
                    <th class="num">Harga Satuan</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($tableRows as $row)
                    <tr>
                        <td>{{ $row['ukuran'] }}</td>
                        <td class="num">{{ $row['jumlah'] }}</td>
                        <td class="num">{{ $row['harga'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        @if ($order->design_file_admin)
            <p class="section-title">Desain</p>
            <div class="design-thumb">
                @if (str_ends_with(strtolower((string) $order->design_file_admin), '.pdf'))
                    <p class="muted">File desain: {{ basename($order->design_file_admin) }} (buka via halaman detail pesanan)</p>
                @else
                    <img src="{{ route('admin.orders.design.preview', $order) }}" alt="Desain pesanan #{{ $order->id }}">
                @endif
            </div>
        @endif

        @if ($order->notes)
            <p class="section-title">Catatan</p>
            <p class="muted" style="white-space: pre-line">{{ $order->notes }}</p>
        @endif

        <div class="sign">
            <div class="box">
                <div>Pihak Produksi</div>
                <div class="line">( {{ $order->assigned_operator ?? '……………………' }} )</div>
            </div>
            <div class="box">
                <div>Banjarmasin, {{ $docDate }}</div>
                <div>Admin Banjar Custome</div>
                <div class="line">( ................................. )</div>
            </div>
        </div>
    </div>

    <div class="actions">
        <button type="button" class="primary" onclick="window.print()">Cetak</button>
        <a href="{{ route('admin.orders.show', $order) }}">Kembali ke Detail</a>
    </div>
</body>
</html>
