<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\MasterOption;
use App\Support\ServiceTypes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Cell\StringCell;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    /**
     * Judul kolom resmi impor/ekspor (urutan tampilan). Pencocokan judul saat
     * impor dinormalisasi case/spasi sehingga urutan bebas & kolom ekstra diabaikan.
     */
    private const IMPORT_HEADERS = [
        'BULAN', 'customer', 'jenis', 'waktu pengambilan', 'type', 'bahan', 'warna',
        'pesan bahan', 'produksi', 'finish', 'TOTAL', 'jumlah', 'ukuran',
        'whatsapp', 'status', 'catatan',
    ];

    /** @var list<string> */
    private const IMPORT_REQUIRED_HEADERS = ['customer'];

    private const IMPORT_MAX_ROWS = 50000;

    public function index(Request $request): View
    {
        $activeStatus = $request->query('status');

        if (! in_array($activeStatus, array_keys(CustomOrder::STATUS_LABELS), true)) {
            $activeStatus = null;
        }

        $activeSource = $request->query('source');

        if (! in_array($activeSource, array_keys(CustomOrder::SOURCE_LABELS), true)) {
            $activeSource = null;
        }

        $query = CustomOrder::with('product')->latest()->orderByDesc('id');

        if ($activeStatus) {
            $query->where('status', $activeStatus);
        }

        if ($activeSource) {
            $query->where('source', $activeSource);
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->trim();

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$search}%");
            });
        }

        $countsQuery = CustomOrder::query();

        if ($activeSource) {
            $countsQuery->where('source', $activeSource);
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(10)->withQueryString(),
            'activeStatus' => $activeStatus,
            'activeSource' => $activeSource,
            'search' => $request->query('q', ''),
            'statusLabels' => CustomOrder::STATUS_LABELS,
            'statusCounts' => [
                'all' => (clone $countsQuery)->count(),
                ...(clone $countsQuery)
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->all(),
            ],
        ]);
    }

    public function export(Request $request): BinaryFileResponse
    {
        $activeStatus = $request->query('status');

        if (! in_array($activeStatus, array_keys(CustomOrder::STATUS_LABELS), true)) {
            $activeStatus = null;
        }

        $activeSource = $request->query('source');

        if (! in_array($activeSource, array_keys(CustomOrder::SOURCE_LABELS), true)) {
            $activeSource = null;
        }

        $query = CustomOrder::query()->latest()->orderByDesc('id');

        if ($activeStatus) {
            $query->where('status', $activeStatus);
        }

        if ($activeSource) {
            $query->where('source', $activeSource);
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->trim();

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$search}%");
            });
        }

        $path = tempnam(sys_get_temp_dir(), 'bc-export');

        $writer = new XlsxWriter;
        $writer->openToFile($path);

        $writer->addRow(new Row(array_map(
            static fn (string $header) => self::xlsxCell($header),
            self::IMPORT_HEADERS,
        )));

        $query->chunk(200, static function ($orders) use ($writer) {
            foreach ($orders as $order) {
                $writer->addRow(new Row(array_map(
                    static fn ($value) => self::xlsxCell($value),
                    self::exportCells($order),
                )));
            }
        });

        $writer->close();

        return response()
            ->download($path, 'laporan-pesanan.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    /**
     * Sel XLSX aman: string selalu ditulis sebagai teks (StringCell) sehingga
     * nilai yang diawali "=" tidak diubah menjadi formula oleh OpenSpout
     * (injeksi formula XLSX — Cell::fromValue mendeteksi prefix "=").
     */
    private static function xlsxCell(mixed $value): Cell
    {
        if (is_string($value)) {
            return new StringCell($value, new Style);
        }

        return Cell::fromValue($value, new Style);
    }

    /**
     * Baris ekspor mengikuti IMPORT_HEADERS (format impor/ekspor yang sama
     * agar hasil ekspor bisa langsung diimpor ulang tanpa kehilangan data).
     *
     * @return list<mixed>
     */
    private static function exportCells(CustomOrder $order): array
    {
        return [
            $order->created_at?->translatedFormat('F Y'),
            $order->name,
            $order->service_type,
            $order->deadline?->format('d/m/Y'),
            $order->type,
            $order->bahan,
            $order->warna,
            $order->pesan_bahan_ok ? 'ok' : 'nok',
            $order->produksi_ok ? 'ok' : 'nok',
            $order->finish_ok ? 'ok' : 'nok',
            $order->resolvedTotalPrice(),
            $order->quantity,
            $order->size_quantities !== null
                ? collect($order->size_quantities)->map(fn ($qty, $size) => $size.':'.$qty)->implode(', ')
                : null,
            $order->whatsapp_number,
            $order->statusLabel(),
            $order->notes,
        ];
    }

    public function show(CustomOrder $order): View
    {
        $order->load(['product', 'statusHistory.changer']);

        return view('admin.orders.show', compact('order'));
    }

    public function print(CustomOrder $order): View
    {
        $order->load('product');

        return view('admin.orders.print', compact('order'));
    }

    public function updateStatus(Request $request, CustomOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:'.implode(',', array_keys(CustomOrder::STATUS_LABELS))],
            'cancel_reason' => ['nullable', 'string', 'min:3', 'max:1000'],
        ], [
            'cancel_reason.min' => 'Alasan pembatalan minimal 3 karakter.',
        ]);

        $to = $validated['status'];
        $previous = $order->status;
        $reason = trim((string) ($validated['cancel_reason'] ?? ''));

        if ($to === CustomOrder::STATUS_CANCELLED && $reason === '') {
            return back()->withErrors([
                'status' => 'Pembatalan pesanan wajib beralasan.',
                'cancel_reason' => 'Isi alasan pembatalan pesanan.',
            ])->onlyInput(['status', 'cancel_reason']);
        }

        if ($to !== $previous && ! $order->canTransitionTo($to)) {
            $allowed = array_map(
                fn (string $key) => CustomOrder::STATUS_LABELS[$key],
                $order->allowedNextStatuses(),
            );

            return back()->withErrors([
                'status' => 'Transisi "'
                    .$order->statusLabel().'" → "'.$order->statusLabelFor($to).'" tidak diizinkan.'
                    .($allowed !== [] ? ' Status yang tersedia: '.implode(', ', $allowed).'.' : ''),
            ])->onlyInput(['status', 'cancel_reason']);
        }

        if ($to === CustomOrder::STATUS_READY && $to !== $previous) {
            $blockers = $order->readyBlockers();

            if ($blockers !== []) {
                return back()->withErrors([
                    'status' => 'Belum bisa naik ke "'
                        .CustomOrder::STATUS_LABELS[CustomOrder::STATUS_READY].'": '.implode(' ', $blockers),
                ])->onlyInput(['status', 'cancel_reason']);
            }
        }

        if ($to === $previous) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('status', 'Status pesanan tidak berubah ("'.$order->statusLabel().'").');
        }

        $payload = ['status' => $to];

        if ($to === CustomOrder::STATUS_CANCELLED) {
            $payload['cancel_reason'] = $reason;
        }

        // Kembali ke tahap desain (revisi) membatalkan persetujuan lama.
        if ($to === CustomOrder::STATUS_DESIGN) {
            $payload['design_approved_at'] = null;
        }

        $order->forceFill($payload)->save();

        $order->statusHistory()->create([
            'from_status' => $previous,
            'to_status' => $to,
            'changed_by' => $request->user()?->id,
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Status pesanan diperbarui menjadi "'.$order->statusLabel().'".');
    }

    public function updateProgress(Request $request, CustomOrder $order): RedirectResponse
    {
        $order->forceFill([
            'pesan_bahan_ok' => $request->boolean('pesan_bahan_ok'),
            'produksi_ok' => $request->boolean('produksi_ok'),
            'finish_ok' => $request->boolean('finish_ok'),
        ])->save();

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Progres produksi pesanan diperbarui.');
    }

    public function downloadDesign(CustomOrder $order): StreamedResponse
    {
        abort_unless($order->design_file, 404);
        abort_unless(Storage::disk('local')->exists($order->design_file), 404);

        return Storage::disk('local')->download($order->design_file, 'desain-'.$order->id.'-'.basename($order->design_file));
    }

    /**
     * Unggah hasil desain dari designer (disk privat). Ganti file =
     * persetujuan pelanggan lama dianggap tidak berlaku.
     */
    public function updateDesign(Request $request, CustomOrder $order): RedirectResponse
    {
        $request->validate([
            'design_file_admin' => ['required', 'file', 'max:10240', 'extensions:jpg,jpeg,png,webp,pdf'],
        ], [
            'design_file_admin.extensions' => 'Format file harus jpg, png, webp, atau pdf.',
        ]);

        $disk = Storage::disk('local');
        $old = $order->design_file_admin;

        $path = $request->file('design_file_admin')->store('designs/admin', 'local');

        $order->forceFill([
            'design_file_admin' => $path,
            'design_approved_at' => null,
        ])->save();

        if ($old !== null && $old !== $path && $disk->exists($old)) {
            $disk->delete($old);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Hasil desain diunggah. Persetujuan pelanggan lama direset — minta setujui ulang.');
    }

    public function approveDesign(CustomOrder $order): RedirectResponse
    {
        if (! in_array($order->status, [CustomOrder::STATUS_DESIGN, CustomOrder::STATUS_READY], true)) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('status', 'Persetujuan desain hanya berlaku pada tahap "'.CustomOrder::STATUS_LABELS[CustomOrder::STATUS_DESIGN].'" / "'.CustomOrder::STATUS_LABELS[CustomOrder::STATUS_READY].'".');
        }

        if ($order->design_approved_at === null) {
            $order->forceFill(['design_approved_at' => now()])->save();

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('status', 'Desain ditandai disetujui (mis. disetujui via WhatsApp tanpa tombol di website).');
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Desain sudah pernah ditandai disetujui.');
    }

    public function designPreview(CustomOrder $order)
    {
        abort_unless($order->design_file_admin, 404);
        abort_unless(Storage::disk('local')->exists($order->design_file_admin), 404);

        $mime = Storage::disk('local')->mimeType($order->design_file_admin);

        return response()->file(
            Storage::disk('local')->path($order->design_file_admin),
            ['Content-Type' => $mime],
        );
    }

    public function updateDp(Request $request, CustomOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'dp_amount' => ['nullable', 'integer', 'min:0', 'max:100000000000'],
        ]);

        $amount = ($validated['dp_amount'] ?? null) !== null && $validated['dp_amount'] !== ''
            ? (int) $validated['dp_amount']
            : null;

        if ($amount === null) {
            $order->forceFill(['dp_amount' => null, 'dp_received_at' => null])->save();

            return redirect()
                ->route('admin.orders.show', $order)
                ->with('status', 'Penanda DP dikosongkan.');
        }

        $wasReceived = $order->dp_received_at !== null;

        $order->forceFill([
            'dp_amount' => $amount,
            'dp_received_at' => $order->dp_received_at ?? now(),
        ])->save();

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'DP Rp '.number_format($amount, 0, ',', '.').' dicatat'.($wasReceived ? '' : ' (diterima sekarang)').'.');
    }

    public function updateHandover(Request $request, CustomOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'assigned_operator' => ['nullable', 'string', 'max:120'],
        ]);

        $order->update([
            'assigned_operator' => ($validated['assigned_operator'] ?? '') !== ''
                ? $validated['assigned_operator']
                : null,
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Operator produksi disimpan.');
    }

    public function cardPrint(CustomOrder $order): View
    {
        $order->load('product');

        return view('admin.orders.card-kerja', compact('order'));
    }

    public function spkPrint(CustomOrder $order): View
    {
        $order->load('product');

        if ($order->spk_number === null) {
            $order->forceFill([
                'spk_number' => 'SPK-'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                'spk_issued_at' => now(),
            ])->save();
        }

        return view('admin.orders.spk', compact('order'));
    }

    public function updatePrice(Request $request, CustomOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'unit_price' => ['nullable', 'integer', 'min:0', 'max:100000000000'],
            'total_price' => ['nullable', 'integer', 'min:0', 'max:100000000000'],
            'size_prices' => ['nullable', 'array', 'max:30'],
            'size_prices.*' => ['nullable', 'integer', 'min:0', 'max:100000000000'],
        ]);

        $unitPrice = ($validated['unit_price'] ?? null) !== null ? (int) $validated['unit_price'] : null;
        $totalPrice = ($validated['total_price'] ?? null) !== null ? (int) $validated['total_price'] : null;

        $order->update([
            'unit_price' => $unitPrice,
            'total_price' => $totalPrice,
            'size_prices' => self::cleanSizePrices($validated['size_prices'] ?? null),
        ]);

        // Total otomatis mengikuti rincian ukuran (Σ qty × harga efektif per
        // ukuran, termasuk surcharge) bila ada; tanpa rincian → satuan × jumlah.
        if ($totalPrice === null && $unitPrice !== null && $order->quantity) {
            $totalPrice = $order->sizeRowsSubtotal()
                ?? $unitPrice * (int) $order->quantity;
            $order->update(['total_price' => $totalPrice]);
        }

        $message = 'Harga pesanan disimpan.';

        if ($totalPrice !== null
            && $unitPrice !== null
            && $order->quantity) {
            $expected = $order->sizeRowsSubtotal()
                ?? $unitPrice * (int) $order->quantity;

            if ($totalPrice !== $expected) {
                $message .= ' Perhatian: total tidak sama dengan jumlah rincian ukuran (satuan × jumlah) — periksa kembali.';
            }
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', $message);
    }

    /**
     * Bersihkan input harga per ukuran: key [A-Za-z0-9] maks 10 char,
     * value kosong/null dibuang (artinya pakai hitungan otomatis).
     *
     * @return array<string, int>|null
     */
    private static function cleanSizePrices(?array $input): ?array
    {
        if ($input === null || $input === []) {
            return null;
        }

        $clean = [];

        foreach ($input as $key => $value) {
            $key = trim((string) $key);

            if ($key === '' || strlen($key) > 10 || ! preg_match('/^[A-Za-z0-9]+$/', $key)) {
                continue;
            }

            if ($value === null || $value === '') {
                continue;
            }

            $clean[$key] = (int) $value;
        }

        return $clean !== [] ? $clean : null;
    }

    public function updateSpec(Request $request, CustomOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'bahan' => ['nullable', 'string', 'max:120'],
            'type' => ['nullable', 'string', 'max:120'],
            'warna' => ['nullable', 'string', 'max:120'],
            'collar_model' => ['nullable', 'string', 'max:60'],
        ]);

        $order->update([
            'bahan' => ($validated['bahan'] ?? null) ?: null,
            'type' => ($validated['type'] ?? null) ?: null,
            'warna' => ($validated['warna'] ?? null) ?: null,
            'collar_model' => ($validated['collar_model'] ?? null) ?: null,
        ]);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Spesifikasi nota disimpan.');
    }

    public function importForm(): View
    {
        $report = session('report');

        if ($report !== null) {
            session()->forget('report');
        }

        return view('admin.orders.import', ['report' => $report]);
    }

    public function importTemplate(): BinaryFileResponse
    {
        $path = tempnam(sys_get_temp_dir(), 'bc-template');

        $writer = new XlsxWriter;
        $writer->openToFile($path);
        $writer->addRow(new Row(array_map(
            static fn (string $header) => self::xlsxCell($header),
            self::IMPORT_HEADERS,
        )));
        $writer->close();

        return response()
            ->download($path, 'template-pesanan.xlsx', [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])
            ->deleteFileAfterSend(true);
    }

    public function importStore(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:2048', 'extensions:xlsx,csv'],
        ], [
            'file.extensions' => 'Format file harus .xlsx atau .csv.',
        ]);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());

        try {
            $rows = $extension === 'csv'
                ? $this->readCsvRows($file->getRealPath())
                : $this->readXlsxRows($file->getRealPath());
        } catch (\Throwable $e) {
            report($e);

            return $this->importRedirect([
                'fatal' => str_starts_with($e->getMessage(), 'File melebihi')
                    ? $e->getMessage()
                    : 'File gagal dibaca. Pastikan file .xlsx/.csv tidak rusak dan baris pertama adalah baris judul kolom sesuai template.',
                'errors' => [],
                'imported' => null,
            ]);
        }

        if ($rows === []) {
            return $this->importRedirect([
                'fatal' => 'File tidak berisi data.',
                'errors' => [],
                'imported' => null,
            ]);
        }

        if (count($rows) > self::IMPORT_MAX_ROWS + 1) {
            return $this->importRedirect([
                'fatal' => 'File melebihi batas '.self::IMPORT_MAX_ROWS.' baris data.',
                'errors' => [],
                'imported' => null,
            ]);
        }

        $map = [];
        $canonical = array_map(fn (string $header) => $this->normalizeHeading($header), self::IMPORT_HEADERS);

        foreach (array_shift($rows) as $index => $header) {
            $key = $this->normalizeHeading((string) $header);

            if (in_array($key, $canonical, true) && ! isset($map[$key])) {
                $map[$key] = $index;
            }
        }

        $missing = array_diff(self::IMPORT_REQUIRED_HEADERS, array_keys($map));

        if ($missing !== []) {
            return $this->importRedirect([
                'fatal' => 'Kolom wajib tidak ditemukan: '.implode(', ', $missing).'. Unduh template resmi lalu isi sesuai judul kolom (urutan boleh bebas).',
                'errors' => [],
                'imported' => null,
            ]);
        }

        $errors = [];
        $warnings = [];
        $records = [];
        $newMaster = [];
        $knownOptions = collect(MasterOption::KINDS)
            ->mapWithKeys(fn (string $kind) => [
                $kind => array_map('mb_strtolower', MasterOption::namesFor($kind)),
            ])
            ->all();

        foreach ($rows as $offset => $cells) {
            $line = $offset + 2;

            if (array_filter($cells, static fn ($v) => trim((string) $v) !== '') === []) {
                continue;
            }

            $get = static fn (string $key): string => isset($map[$key], $cells[$map[$key]])
                ? trim((string) $cells[$map[$key]])
                : '';

            $rowErrors = [];

            $customer = $get('customer');

            if ($customer === '' || mb_strlen($customer) > 255) {
                $rowErrors[] = 'customer wajib diisi (maks 255 karakter).';
            }

            $jenis = $get('jenis');

            if ($jenis !== '') {
                if (mb_strlen($jenis) > 60) {
                    $rowErrors[] = 'Jenis maks 60 karakter.';
                    $jenis = null;
                } else {
                    $matched = null;

                    foreach (ServiceTypes::titles() as $title) {
                        if (mb_strtolower($title) === mb_strtolower($jenis)) {
                            $matched = $title;
                            break;
                        }
                    }

                    if ($matched === null) {
                        $rowErrors[] = 'Jenis "'.$jenis.'" tidak dikenal — gunakan jenis yang terdaftar di Pengaturan → Jenis Sablon & Teknik.';
                        $jenis = null;
                    } else {
                        $jenis = $matched;
                    }
                }
            } else {
                $jenis = null;
            }

            $deadline = null;
            $rawDeadline = $get('waktu pengambilan');

            if ($rawDeadline !== '') {
                $deadline = $this->parseDate($rawDeadline);

                if ($deadline === null) {
                    $rowErrors[] = 'Waktu pengambilan tidak valid (pakai d/m/Y atau Y-m-d).';
                }
            }

            $specs = ['type' => null, 'bahan' => null, 'warna' => null];

            foreach ([
                ['type', MasterOption::KIND_TYPE],
                ['bahan', MasterOption::KIND_MATERIAL],
                ['warna', MasterOption::KIND_COLOR],
            ] as [$column, $kind]) {
                $value = $get($column);

                if ($value === '') {
                    continue;
                }

                if (mb_strlen($value) > 120) {
                    $rowErrors[] = 'Kolom '.$column.' maks 120 karakter.';

                    continue;
                }

                $specs[$column] = $value;
                $lower = mb_strtolower($value);

                if (! in_array($lower, $knownOptions[$kind], true)) {
                    $knownOptions[$kind][] = $lower;
                    $newMaster[$kind.'|'.$lower] = ['kind' => $kind, 'name' => $value];
                }
            }

            $flags = [];

            foreach (CustomOrder::PROGRESS_FLAGS as $field => $label) {
                $heading = str_replace('_', ' ', str_replace('_ok', '', $field));
                [$flag, $flagError] = $this->parseFlag($get($heading));

                if ($flagError !== null) {
                    $rowErrors[] = 'Kolom "'.$heading.'" '.$flagError;
                }

                $flags[$field] = $flag;
            }

            $jumlah = null;
            $rawJumlah = $get('jumlah');

            if ($rawJumlah !== '') {
                $jumlah = filter_var($rawJumlah, FILTER_VALIDATE_INT);

                if ($jumlah === false || $jumlah < 1 || $jumlah > 1000000) {
                    $rowErrors[] = 'Jumlah harus bilangan bulat 1-1000000.';
                    $jumlah = null;
                }
            }

            [$sizes, $sizeError] = $this->parseSizeQuantities($get('ukuran'));

            if ($sizeError !== null) {
                $rowErrors[] = $sizeError;
            }

            if ($sizes !== null && $jumlah === null) {
                $jumlah = array_sum($sizes);
            }

            $total = null;
            $rawTotal = $get('total');

            if ($rawTotal !== '') {
                $total = $this->parseAmount($rawTotal);

                if ($total === null) {
                    $rowErrors[] = 'TOTAL tidak valid (angka saja, maks Rp100.000.000.000).';
                }
            }

            $whatsapp = $get('whatsapp');

            if ($whatsapp !== ''
                && (! preg_match('/^\+?[\d][\d\s()+-]{4,31}$/', $whatsapp)
                    || ! preg_match('/\d{5,}/', $whatsapp))) {
                $rowErrors[] = 'WhatsApp tidak valid (minimal 5 angka).';
            }

            $status = CustomOrder::STATUS_PENDING;
            $rawStatus = mb_strtolower($get('status'));

            if ($rawStatus !== '') {
                if (array_key_exists($rawStatus, CustomOrder::STATUS_LABELS)) {
                    $status = $rawStatus;
                } else {
                    $byLabel = array_search($rawStatus, array_map('mb_strtolower', CustomOrder::STATUS_LABELS), true);

                    if ($byLabel !== false) {
                        $status = $byLabel;
                    } else {
                        $rowErrors[] = 'Status harus salah satu dari: '.implode(', ', array_keys(CustomOrder::STATUS_LABELS)).'.';
                    }
                }
            }

            $catatan = $get('catatan');

            if (mb_strlen($catatan) > 1000) {
                $rowErrors[] = 'Catatan maks 1000 karakter.';
                $catatan = '';
            }

            $createdAt = null;
            $rawBulan = $get('bulan');

            if ($rawBulan !== '') {
                $createdAt = $this->parseMonth($rawBulan);

                if ($createdAt === null) {
                    $warnings[] = 'Baris '.$line.': BULAN "'.$rawBulan.'" tidak dikenal, tanggal pesanan diisi tanggal impor.';
                }
            }

            if ($rowErrors !== []) {
                $errors[] = ['baris' => $line, 'pesan' => implode(' ', $rowErrors)];

                continue;
            }

            $record = [
                'name' => $customer,
                'whatsapp_number' => $whatsapp ?: null,
                'quantity' => $jumlah,
                'status' => $status,
                'service_type' => $jenis,
                'type' => $specs['type'],
                'bahan' => $specs['bahan'],
                'warna' => $specs['warna'],
                'size_quantities' => $sizes,
                'total_price' => $total,
                'notes' => $catatan ?: null,
                'pesan_bahan_ok' => $flags['pesan_bahan_ok'],
                'produksi_ok' => $flags['produksi_ok'],
                'finish_ok' => $flags['finish_ok'],
                'source' => CustomOrder::SOURCE_IMPORT,
                'deadline' => $deadline,
            ];

            // BULAN menurunkan tanggal pesanan; kosong/tak terparse → default now.
            if ($createdAt !== null) {
                $record['created_at'] = $createdAt;
            }

            $records[] = $record;
        }

        if ($errors !== []) {
            return $this->importRedirect([
                'fatal' => null,
                'errors' => $errors,
                'warnings' => $warnings,
                'imported' => null,
            ]);
        }

        if ($records === []) {
            return $this->importRedirect([
                'fatal' => 'File tidak berisi baris data.',
                'errors' => [],
                'warnings' => $warnings,
                'imported' => null,
            ]);
        }

        $imported = DB::transaction(static function () use ($records, $newMaster) {
            foreach ($newMaster as $entry) {
                MasterOption::firstOrCreate([
                    'kind' => $entry['kind'],
                    'name' => $entry['name'],
                ]);
            }

            foreach ($records as $record) {
                $order = new CustomOrder;
                $order->forceFill($record);
                $order->save();
            }

            return count($records);
        });

        return $this->importRedirect([
            'fatal' => null,
            'errors' => [],
            'warnings' => $warnings,
            'created' => array_values($newMaster),
            'imported' => $imported,
        ]);
    }

    private function importRedirect(array $report): RedirectResponse
    {
        $report = array_merge([
            'fatal' => null,
            'errors' => [],
            'warnings' => [],
            'created' => [],
            'imported' => null,
        ], $report);

        return redirect()
            ->route('admin.orders.import')
            ->with('report', $report);
    }

    /** Normalisasi judul kolom: huruf kecil, spasi tunggal, underscore jadi spasi. */
    private function normalizeHeading(string $heading): string
    {
        $heading = str_replace(["\xEF\xBB\xBF", "\r", "\n", "\t", '_'], ' ', $heading);

        return preg_replace('/\s+/', ' ', mb_strtolower(trim($heading))) ?? '';
    }

    /**
     * Parse sel flag produksi: ok (setuju) / nok (belum) + turunan umum.
     *
     * @return array{0: bool, 1: string|null} [nilai, pesan error bila tidak dikenal]
     */
    private function parseFlag(string $value): array
    {
        $value = mb_strtolower(trim($value));

        if ($value === '') {
            return [false, null];
        }

        if (in_array($value, ['ok', '1', 'y', 'yes', 'ya', 'true', 'v'], true)) {
            return [true, null];
        }

        if (in_array($value, ['nok', '0', 'n', 'no', 'tidak', 'false', 'x'], true)) {
            return [false, null];
        }

        return [false, 'harus bernilai ok atau nok.'];
    }

    /**
     * Parse kolom "BULAN" menjadi tanggal pesanan (tgl 1 bulan tsb).
     * Format diterima: "Oktober 2026", "Okt 2026", "October 2026", "2026-10", "10/2026", "10-2026".
     */
    private function parseMonth(string $value): ?\DateTimeInterface
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        $months = [
            'januari' => 1, 'jan' => 1, 'january' => 1,
            'februari' => 2, 'pebruari' => 2, 'feb' => 2, 'february' => 2,
            'maret' => 3, 'mar' => 3, 'march' => 3,
            'april' => 4, 'apr' => 4,
            'mei' => 5, 'may' => 5,
            'juni' => 6, 'jun' => 6, 'june' => 6,
            'juli' => 7, 'jul' => 7, 'july' => 7,
            'agustus' => 8, 'agu' => 8, 'agust' => 8, 'august' => 8, 'aug' => 8,
            'september' => 9, 'sept' => 9, 'sep' => 9,
            'oktober' => 10, 'okt' => 10, 'october' => 10, 'oct' => 10,
            'november' => 11, 'nov' => 11,
            'desember' => 12, 'des' => 12, 'december' => 12, 'dec' => 12,
        ];

        $month = null;
        $year = null;

        if (preg_match('/^([a-z]+)\s+(\d{4})$/i', $value, $matches)) {
            $month = $months[mb_strtolower($matches[1])] ?? null;
            $year = (int) $matches[2];
        } elseif (preg_match('/^(\d{4})-(\d{1,2})$/', $value, $matches)) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
        } elseif (preg_match('/^(\d{1,2})[\/\-.](\d{4})$/', $value, $matches)) {
            $month = (int) $matches[1];
            $year = (int) $matches[2];
        }

        if ($month === null || $year === null || $month < 1 || $month > 12) {
            return null;
        }

        if ($year < 1970 || $year > now()->year + 2) {
            return null;
        }

        return Carbon::create($year, $month, 1, 0, 0, 0);
    }

    /**
     * Parse kolom "ukuran" format "S:6, M:12" menjadi [ukuran => qty].
     *
     * @return array{0: array<string, int>|null, 1: string|null} [ukuran, pesan error]
     */
    private function parseSizeQuantities(string $value): array
    {
        $value = trim($value);

        if ($value === '') {
            return [null, null];
        }

        $result = [];

        foreach (preg_split('/[,;\n]+/', $value) ?: [] as $part) {
            $part = trim($part);

            if ($part === '') {
                continue;
            }

            if (! preg_match('/^([^:]+):\s*(\d+)$/', $part, $matches)) {
                return [null, 'Ukuran "'.$part.'" tidak valid (format: S:6, M:12).'];
            }

            $size = trim($matches[1]);
            $qty = (int) $matches[2];

            if ($size === '' || mb_strlen($size) > 16 || $qty < 1 || $qty > 1000000) {
                return [null, 'Ukuran "'.$part.'" tidak valid (qty 1-1000000, maks 16 karakter).'];
            }

            if (count($result) >= 30) {
                return [null, 'Jumlah baris ukuran maksimal 30.'];
            }

            $result[$size] = ($result[$size] ?? 0) + $qty;
        }

        if ($result === []) {
            return [null, 'Ukuran tidak valid (format: S:6, M:12).'];
        }

        return [$result, null];
    }

    private function parseDate(string $value): ?\DateTimeInterface
    {
        if ($value === '') {
            return null;
        }

        foreach (['!Y-m-d', '!d/m/Y', '!d-m-Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $value);
            } catch (\Throwable) {
                continue;
            }

            // createFromFormat tidak gagal untuk tanggal rollover (31/02/2026
            // menjadi 3 Maret) — cek warning bawaan DateTime.
            $errors = \DateTime::getLastErrors();

            if (is_array($errors) && ($errors['warning_count'] > 0 || $errors['error_count'] > 0)) {
                continue;
            }

            if ($date->format('Y') < '1970' || $date->format('Y') > (string) (now()->year + 2)) {
                continue;
            }

            return $date;
        }

        return null;
    }

    /**
     * Parse nominal uang dari sel spreadsheet. Hanya menerima angka polos atau
     * pemisah ribuan yang tegas (1.500.000 / 1,500,000) — desimal, rentang,
     * dan teks bebas ditolak agar tidak diam-diam salah hitung.
     */
    private function parseAmount(string $value): ?int
    {
        $value = trim($value);

        if (preg_match('/^rp\s*/i', $value)) {
            $value = trim(substr($value, strlen('rp')));
        }

        if ($value === '' || ! preg_match('/^[0-9.,\s]{1,20}$/', $value)) {
            return null;
        }

        if (preg_match('/^\d{1,3}(?:[.,]\d{3})+$/', $value)) {
            $value = str_replace(['.', ',', ' '], '', $value);
        } elseif (preg_match('/^\d+$/', str_replace(' ', '', $value))) {
            $value = str_replace(' ', '', $value);
        } else {
            return null;
        }

        $amount = (int) $value;

        if ($amount > 100000000000) {
            return null;
        }

        return $amount;
    }

    private function cellToString(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y');
        }

        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        if (is_float($value)) {
            return $value == (int) $value ? (string) (int) $value : (string) $value;
        }

        return (string) $value;
    }

    private function readXlsxRows(string $path): array
    {
        $reader = new XlsxReader;
        $reader->open($path);

        $rows = [];

        try {
            foreach ($reader->getSheetIterator() as $sheet) {
                foreach ($sheet->getRowIterator() as $row) {
                    $rows[] = array_map($this->cellToString(...), $row->toArray());

                    if (count($rows) > self::IMPORT_MAX_ROWS + 1) {
                        throw new \RuntimeException('File melebihi batas '.self::IMPORT_MAX_ROWS.' baris data.');
                    }
                }

                break;
            }
        } finally {
            $reader->close();
        }

        return $rows;
    }

    private function readCsvRows(string $path): array
    {
        $firstLine = strtok((string) file_get_contents($path), "\r\n") ?: '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $handle = fopen($path, 'rb');
        $rows = [];

        while (($cells = fgetcsv($handle, 0, $delimiter, '"', '')) !== false) {
            // Baris kosong tetap disimpan agar nomor baris pada pesan error
            // (offset + 2) sama dengan nomor baris asli di file.
            $rows[] = array_map($this->cellToString(...), $cells);
        }

        fclose($handle);

        return $rows;
    }
}
