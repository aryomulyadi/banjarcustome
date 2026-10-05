<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Nota Pesanan #{{ $order->id }} — Banjar Custome</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: Georgia, 'Times New Roman', serif; color: #1c1917; margin: 0; padding: 32px; background: #fff; }
        .nota { max-width: 720px; margin: 0 auto; border: 1px solid #d6d3d1; padding: 32px; }
        h1 { font-size: 22px; margin: 0 0 4px; }
        .muted { color: #78716c; font-size: 13px; }
        .head { display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px solid #1c1917; padding-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { text-align: left; padding: 8px 10px; border-bottom: 1px solid #e7e5e4; vertical-align: top; }
        th { width: 35%; font-weight: 600; color: #44403c; }
        .badge { display: inline-block; padding: 2px 10px; border: 1px solid #1c1917; border-radius: 999px; font-size: 12px; text-transform: uppercase; }
        .foot { margin-top: 28px; font-size: 12px; color: #78716c; display: flex; justify-content: space-between; gap: 12px; }
        .actions { max-width: 720px; margin: 16px auto 0; display: flex; gap: 8px; }
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
                <p class="muted">#{{ $order->id }}</p>
                <span class="badge">{{ $order->statusLabel() }}</span>
            </div>
        </div>

        <table>
            <tr><th>Tanggal</th><td>{{ $order->created_at->translatedFormat('d F Y, H:i') }} WITA</td></tr>
            <tr><th>Pelanggan</th><td>{{ $order->name }}</td></tr>
            <tr><th>WhatsApp</th><td>{{ $order->whatsapp_number }}</td></tr>
            <tr><th>Produk</th><td>{{ $order->product?->title ?? 'Custom' }}</td></tr>
            <tr><th>Jumlah</th><td>{{ $order->quantity ? $order->quantity.' pcs' : '—' }}</td></tr>
            @if ($order->size_quantities)
                <tr>
                    <th>Per ukuran</th>
                    <td>
                        @foreach ($order->size_quantities as $size => $qty)
                            {{ $size }}: {{ $qty }}{{ $loop->last ? '' : ', ' }}
                        @endforeach
                    </td>
                </tr>
            @endif
            <tr><th>Jenis pengerjaan</th><td>{{ $order->service_type ?? '—' }}</td></tr>
            <tr><th>Deadline</th><td>{{ $order->deadline?->translatedFormat('d F Y') ?? '—' }}</td></tr>
            <tr><th>Estimasi</th><td>{{ $order->is_express ? 'EXPRESS — same-day / di bawah 10 hari' : 'Reguler — kaos 3-7 hari kerja, jersey 10-12 hari' }}</td></tr>
            <tr><th>Pengiriman</th><td>{{ $order->delivery_method === 'ambil' ? 'Ambil sendiri' : ($order->delivery_method === 'kirim' ? 'Dikirim' : '—') }}</td></tr>
            @if ($order->address)
                <tr><th>Alamat</th><td>{{ $order->address }}</td></tr>
            @endif
            <tr><th>Detail</th><td>{{ $order->order_details ?? '—' }}</td></tr>
            @if ($order->notes)
                <tr><th>Catatan</th><td>{{ $order->notes }}</td></tr>
            @endif
        </table>

        <div class="foot">
            <span>Dokumen ini dicetak dari sistem Banjar Custome.</span>
            <span>Harga &amp; pembayaran menyusul konfirmasi CS. Skema: DP 50% di awal, 50% pelunasan sebelum diambil/dikirim.</span>
        </div>
    </div>

    <div class="actions">
        <button type="button" class="primary" onclick="window.print()">Cetak</button>
        <a href="{{ route('admin.orders.show', $order) }}">Kembali</a>
    </div>
</body>
</html>
