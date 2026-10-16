{{--
    Tabel perhitungan nota / penawaran.
    Kolom: Jenis | Bahan | Type | Warna | Ukuran | Jumlah | Harga | Total Harga

    Pakai:
        @include('admin.orders._nota-table', ['rows' => [
            ['jenis' => ..., 'bahan' => ..., 'type' => ..., 'warna' => ...,
             'ukuran' => ..., 'jumlah' => ..., 'harga' => ..., 'total' => ...],
        ]])

    Halaman pemakai menyediakan CSS untuk .nota-table (lihat print.blade.php).
--}}
<table class="nota-table">
    <thead>
        <tr>
            <th>Jenis</th>
            <th>Bahan</th>
            <th>Type</th>
            <th>Warna</th>
            <th>Ukuran</th>
            <th class="num">Jumlah</th>
            <th class="num">Harga</th>
            <th class="num">Total Harga</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($rows as $row)
            <tr>
                <td>{{ $row['jenis'] ?? '—' }}</td>
                <td>{{ $row['bahan'] ?? '—' }}</td>
                <td>{{ $row['type'] ?? '—' }}</td>
                <td>{{ $row['warna'] ?? '—' }}</td>
                <td class="wrap">{{ $row['ukuran'] ?? '—' }}</td>
                <td class="num">{{ $row['jumlah'] ?? '—' }}</td>
                <td class="num">{{ $row['harga'] ?? '—' }}</td>
                <td class="num">{{ $row['total'] ?? '—' }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
