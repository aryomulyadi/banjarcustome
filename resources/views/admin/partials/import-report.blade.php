@php
    $masterLabels = $masterLabels ?? [
        'type' => 'Type',
        'material' => 'Bahan',
        'color' => 'Warna',
        'collar' => 'Kerah Jersey',
        'fabric' => 'Bahan Jersey',
        'designer' => 'Desainer',
        'jersey_model' => 'Model Jersey',
        'jersey_status' => 'Status Jersey',
        'target_line' => 'Jenis Target',
        'target_type' => 'Type Target',
    ];
@endphp

@isset($report)
    @if ($report['fatal'])
        <div class="mt-4 rounded-xl border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive" role="alert">
            {{ $report['fatal'] }}
        </div>
    @endif

    @if ($report['errors'] !== [])
        <div class="mt-4 rounded-xl border border-destructive/40 bg-destructive/10 px-4 py-3 text-sm text-destructive" role="alert">
            <p class="font-semibold">Impor dibatalkan — {{ count($report['errors']) }} baris tidak valid. Tidak ada data yang masuk ke database.</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                @foreach ($report['errors'] as $error)
                    <li>Baris {{ $error['baris'] }}: {{ $error['pesan'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($report['imported'] !== null)
        <div class="mt-4 rounded-xl border border-emerald-600/40 bg-emerald-600/10 px-4 py-3 text-sm text-emerald-700 dark:text-emerald-400" role="status">
            <p>Berhasil mengimpor {{ $report['imported'] }} data. Semua baris divalidasi dan tersimpan dalam satu transaksi.</p>

            @if (! empty($report['created']))
                <p class="mt-2 font-semibold">Master data baru dibuat otomatis ({{ count($report['created']) }}):</p>
                <ul class="mt-1 list-disc space-y-0.5 pl-5">
                    @foreach ($report['created'] as $master)
                        <li>{{ $masterLabels[$master['kind']] ?? $master['kind'] }}: {{ $master['name'] }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    @if (! empty($report['warnings']))
        <div class="mt-4 rounded-xl border border-amber-600/40 bg-amber-600/10 px-4 py-3 text-sm text-amber-700 dark:text-amber-400" role="status">
            <p class="font-semibold">Perhatian ({{ count($report['warnings']) }}):</p>
            <ul class="mt-1 list-disc space-y-0.5 pl-5">
                @foreach ($report['warnings'] as $warning)
                    <li>{{ $warning }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endisset
