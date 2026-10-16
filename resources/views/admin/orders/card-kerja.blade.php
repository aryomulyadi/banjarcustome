@include('admin.orders._work-card', [
    'docTitle' => 'KARTU KERJA PRODUKSI',
    'docNumber' => null,
    'docDate' => now()->translatedFormat('d F Y'),
    'showOperator' => false,
])
