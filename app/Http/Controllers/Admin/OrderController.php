<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use App\Models\MasterOption;
use App\Models\Product;
use App\Support\ServiceTypes;
use App\Support\Spreadsheet;
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

    /**
     * Judul alternatif per kolom kanonik (template tak dipaksa — judul dicocokkan
     * fleksibel via Spreadsheet::mapHeadings; kedua sisi dinormalisasi dulu).
     */
    private const IMPORT_HEADER_ALIASES = [
        'BULAN' => ['bulan pesanan', 'bulan input'],
        'customer' => ['nama customer', 'pelanggan', 'nama pelanggan'],
        'jenis' => ['jenis pesanan', 'jenis order'],
        'waktu pengambilan' => ['deadline', 'pengambilan', 'waktu ambil', 'tanggal ambil'],
        'type' => ['tipe'],
        'bahan' => ['material'],
        'warna' => ['color'],
        'pesan bahan' => ['pesanan bahan'],
        'produksi' => ['progres produksi', 'progres'],
        'finish' => ['finishing'],
        'TOTAL' => ['total harga', 'nominal'],
        'jumlah' => ['qty', 'kuantitas'],
        'ukuran' => ['size'],
        'whatsapp' => ['no hp', 'nomor hp', 'no. hp', 'wa', 'no whatsapp', 'nomor whatsapp'],
        'status' => ['status pesanan'],
        'catatan' => ['note', 'notes', 'keterangan'],
    ];

    private const IMPORT_MAX_ROWS = 50000;

    public function index(Request $request): View
    {
        [$activeStatus, $activeSource, $activeBulan, $activeJenis, $search] = $this->parseFilters($request);

        $query = CustomOrder::with('product')->latest()->orderByDesc('id');
        $this->applyFilters($query, $activeStatus, $activeSource, $activeBulan, $activeJenis, $search);

        $countsQuery = CustomOrder::query();
        $this->applyFilters($countsQuery, null, $activeSource, $activeBulan, $activeJenis, $search);

        $jenisBase = CustomOrder::query();
        $this->applyFilters($jenisBase, $activeStatus, $activeSource, $activeBulan, null, $search);

        $jenisCounts = [];

        foreach ((clone $jenisBase)
            ->whereNotNull('service_type')
            ->selectRaw('service_type, count(*) as total')
            ->groupBy('service_type')
            ->pluck('total', 'service_type') as $name => $total) {
            $jenisCounts[(string) $name] = (int) $total;
        }

        $jenisCounts['kosong'] = (clone $jenisBase)->whereNull('service_type')->count();

        return view('admin.orders.index', [
            'orders' => $query->paginate(10)->withQueryString(),
            'activeStatus' => $activeStatus,
            'activeSource' => $activeSource,
            'activeBulan' => $activeBulan,
            'activeJenis' => $activeJenis,
            'bulanOptions' => $this->bulanOptions(),
            'jenisCounts' => $jenisCounts,
            'search' => $search,
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
        [$activeStatus, $activeSource, $activeBulan, $activeJenis, $search] = $this->parseFilters($request);

        $query = CustomOrder::query()->latest()->orderByDesc('id');
        $this->applyFilters($query, $activeStatus, $activeSource, $activeBulan, $activeJenis, $search);

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
     * Baca & normalisasi filter bersama index/export.
     *
     * @return array{0: ?string, 1: ?string, 2: ?string, 3: list<string>, 4: string}
     *                                                                               [status, source, bulan YYYY-MM, jenis[], q]
     */
    private function parseFilters(Request $request): array
    {
        $status = $request->query('status');

        if (! in_array($status, array_keys(CustomOrder::STATUS_LABELS), true)) {
            $status = null;
        }

        $source = $request->query('source');

        if (! in_array($source, array_keys(CustomOrder::SOURCE_LABELS), true)) {
            $source = null;
        }

        $bulan = $request->query('bulan');

        if (! is_string($bulan) || ! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $bulan)) {
            $bulan = null;
        }

        $rawJenis = $request->query('jenis');
        $jenis = [];

        if (is_array($rawJenis)) {
            foreach ($rawJenis as $value) {
                if (is_string($value) && $value !== '' && strlen($value) <= 60) {
                    $jenis[] = $value;
                }
            }
        } elseif (is_string($rawJenis) && $rawJenis !== '' && strlen($rawJenis) <= 60) {
            $jenis[] = $rawJenis;
        }

        $jenis = array_values(array_unique($jenis));

        $search = $request->filled('q') ? trim((string) $request->query('q')) : '';

        return [$status, $source, $bulan, $jenis, $search];
    }

    /**
     * Terapkan filter ke query pesanan. `kosong` pada jenis = service_type NULL.
     *
     * @param  list<string>  $jenis
     */
    private function applyFilters(
        $query,
        ?string $status,
        ?string $source,
        ?string $bulan,
        ?array $jenis,
        string $search,
    ): void {
        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($source !== null) {
            $query->where('source', $source);
        }

        if ($bulan !== null) {
            $start = Carbon::parse($bulan.'-01');
            $query->whereBetween('created_at', [$start, $start->copy()->endOfMonth()]);
        }

        if ($jenis !== null && $jenis !== []) {
            $names = array_values(array_filter($jenis, static fn ($value) => $value !== 'kosong'));
            $hasKosong = in_array('kosong', $jenis, true);

            $query->where(function ($builder) use ($names, $hasKosong) {
                if ($names !== []) {
                    $builder->whereIn('service_type', $names);
                }

                if ($hasKosong) {
                    $builder->orWhereNull('service_type');
                }
            });
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Opsi filter bulan dari rentang tanggal pesanan (portabel sqlite/mysql).
     *
     * @return list<string>
     */
    private function bulanOptions(): array
    {
        $min = CustomOrder::min('created_at');
        $max = CustomOrder::max('created_at');

        if ($min === null || $max === null) {
            return [];
        }

        $options = [];
        $cursor = Carbon::parse($min)->startOfMonth();
        $end = Carbon::parse($max)->startOfMonth();

        while ($cursor->lte($end) && count($options) < 120) {
            $options[] = $cursor->format('Y-m');
            $cursor = $cursor->copy()->addMonth();
        }

        return array_reverse($options);
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

    public function destroy(CustomOrder $order): RedirectResponse
    {
        $id = $order->id;
        $customer = $order->name;

        if ($order->design_file && Storage::disk('local')->exists($order->design_file)) {
            Storage::disk('local')->delete($order->design_file);
        }

        if ($order->design_file_admin && Storage::disk('public')->exists($order->design_file_admin)) {
            Storage::disk('public')->delete($order->design_file_admin);
        }

        $order->delete();

        return redirect()
            ->route('admin.orders.index')
            ->with('status', "Pesanan #{$id} ({$customer}) berhasil dihapus.");
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

        if ($order->isFinalStatus()) {
            return back()->withErrors([
                'status' => 'Status "'.$order->statusLabel().'" sudah final dan tidak bisa diubah.',
            ])->onlyInput(['status', 'cancel_reason']);
        }

        if ($to === CustomOrder::STATUS_CANCELLED && $reason === '') {
            return back()->withErrors([
                'status' => 'Pembatalan pesanan wajib beralasan.',
                'cancel_reason' => 'Isi alasan pembatalan pesanan.',
            ])->onlyInput(['status', 'cancel_reason']);
        }

        if ($to !== CustomOrder::STATUS_CANCELLED && ! in_array($to, $order->stages(), true)) {
            return back()->withErrors([
                'status' => 'Tahap "'.$order->statusLabelFor($to).'" tidak tersedia untuk pesanan '
                    .($order->isJersey() ? 'jersey' : 'kaos/lain').'.',
            ])->onlyInput(['status', 'cancel_reason']);
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
            ->to($request->input('redirect') === 'queue'
                ? route('admin.produksi')
                : route('admin.orders.show', $order))
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
        $tooEarly = in_array($order->status, [
            CustomOrder::STATUS_PENDING,
            CustomOrder::STATUS_CONFIRMED,
            CustomOrder::STATUS_COMPLETED,
            CustomOrder::STATUS_CANCELLED,
        ], true);

        if ($tooEarly) {
            return redirect()
                ->route('admin.orders.show', $order)
                ->with('status', 'Persetujuan desain hanya berlaku pada tahap "'
                    .CustomOrder::STATUS_LABELS[CustomOrder::STATUS_DESIGN].'" dan setelahnya (sebelum Selesai/Dibatalkan).');
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
            'spec_groups' => ['nullable', 'array', 'max:20'],
            'spec_groups.*.jenis' => ['nullable', 'string', 'max:60'],
            'spec_groups.*.bahan' => ['nullable', 'string', 'max:120'],
            'spec_groups.*.type' => ['nullable', 'string', 'max:120'],
            'spec_groups.*.warna' => ['nullable', 'string', 'max:120'],
            'spec_groups.*.rows' => ['nullable', 'array', 'max:100'],
            'spec_groups.*.rows.*.size' => ['nullable', 'string', 'max:20'],
            'spec_groups.*.rows.*.qty' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'spec_groups.*.rows.*.unit' => ['nullable', 'integer', 'min:0', 'max:100000000000'],
        ]);

        $specGroups = self::normalizeSpecGroups($validated['spec_groups'] ?? null);

        $data = [
            'bahan' => ($validated['bahan'] ?? null) ?: null,
            'type' => ($validated['type'] ?? null) ?: null,
            'warna' => ($validated['warna'] ?? null) ?: null,
            'collar_model' => ($validated['collar_model'] ?? null) ?: null,
            'spec_groups' => $specGroups,
        ];

        // Selaras saat blok multi-spesifikasi terisi: qty per ukuran & total qty
        // dipakai template WA, kartu progres, dan export.
        if ($specGroups !== null) {
            $sizeQuantities = [];
            $quantity = 0;

            foreach ($specGroups as $group) {
                foreach ($group['rows'] as $row) {
                    $quantity += $row['qty'];

                    if ($row['size'] !== '') {
                        $sizeQuantities[$row['size']] = ($sizeQuantities[$row['size']] ?? 0) + $row['qty'];
                    }
                }
            }

            $data['size_quantities'] = $sizeQuantities !== [] ? $sizeQuantities : null;
            $data['quantity'] = $quantity;
        }

        $order->update($data);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Spesifikasi nota disimpan.');
    }

    public function create(): View
    {
        $products = Product::orderBy('title')->get();
        $serviceTypes = ServiceTypes::titles();

        return view('admin.orders.create', compact('products', 'serviceTypes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:32', 'regex:/^[0-9+\-\s()]+$/'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'service_type' => ['nullable', 'string', 'max:60'],
            'unit_price' => ['nullable', 'integer', 'min:0', 'max:100000000000'],
            'total_price' => ['nullable', 'integer', 'min:0', 'max:100000000000'],
            'status' => ['nullable', 'in:pending,confirmed,ready,production,completed,cancelled'],
            'delivery_method' => ['nullable', 'in:kirim,ambil'],
            'address' => ['required_if:delivery_method,kirim', 'nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'order_details' => ['nullable', 'string', 'min:10', 'max:3000'],
            'size_quantities' => ['nullable', 'array'],
            'size_quantities.*' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'express' => ['nullable', 'boolean'],
        ], [
            'whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka dan tanda + - ( ).',
            'deadline.after_or_equal' => 'Deadline tidak boleh sebelum hari ini.',
            'address.required_if' => 'Alamat pengiriman wajib diisi jika pesanan dikirim.',
            'order_details.min' => 'Detail pesanan minimal 10 karakter.',
            'size_quantities' => 'Rincian ukuran tidak valid.',
        ]);

        // Auto-fill service_type dari produk jika kosong
        if (empty($validated['service_type']) && ! empty($validated['product_id'])) {
            $product = Product::find($validated['product_id']);
            if ($product) {
                $validated['service_type'] = mb_substr($product->title, 0, 60);
            }
        }

        // Normalize size_quantities
        $sizeQuantities = collect($validated['size_quantities'] ?? [])->filter(fn ($qty) => $qty > 0);
        $validated['size_quantities'] = $sizeQuantities->all() ?: null;

        // Auto-fill quantity jika size_quantities ada
        if (! empty($validated['size_quantities']) && empty($validated['quantity'])) {
            $validated['quantity'] = (int) array_sum($validated['size_quantities']);
        }

        // Jika tidak ada harga, set status ke pending
        if ($validated['unit_price'] === null && $validated['total_price'] === null) {
            $validated['status'] = CustomOrder::STATUS_PENDING;
        }

        // Handle design file (opsional, untuk admin)
        if ($request->hasFile('design_file')) {
            $validated['design_file'] = $request->file('design_file')->store('designs', 'local');
        }

        $validated['is_express'] = $request->boolean('express');

        $order = CustomOrder::create($validated);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', "Pesanan baru #{$order->id} berhasil dibuat.");
    }

    /**
     * Buang blok/baris kosong & normalisasi angka; hasil = JSON siap simpan atau null.
     *
     * @param  array<array-key, mixed>|null  $input
     * @return list<array{jenis: string, bahan: string, type: string, warna: string, rows: list<array{size: string, qty: int, unit: int|null}>}>|null
     */
    private static function normalizeSpecGroups(?array $input): ?array
    {
        if ($input === null || $input === []) {
            return null;
        }

        $groups = [];

        foreach ($input as $group) {
            if (! is_array($group)) {
                continue;
            }

            $rows = [];

            foreach (is_array($group['rows'] ?? null) ? $group['rows'] : [] as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $size = trim((string) ($row['size'] ?? ''));
                $qty = filter_var($row['qty'] ?? null, FILTER_VALIDATE_INT);

                if ($qty === false || $qty < 1) {
                    continue;
                }

                $unit = $row['unit'] ?? null;
                $unit = ($unit === null || $unit === '') ? null : filter_var($unit, FILTER_VALIDATE_INT);

                if ($unit === false) {
                    $unit = null;
                }

                $rows[] = [
                    'size' => mb_substr($size, 0, 20),
                    'qty' => $qty,
                    'unit' => $unit,
                ];
            }

            if ($rows === []) {
                continue;
            }

            $groups[] = [
                'jenis' => mb_substr(trim((string) ($group['jenis'] ?? '')), 0, 60),
                'bahan' => mb_substr(trim((string) ($group['bahan'] ?? '')), 0, 120),
                'type' => mb_substr(trim((string) ($group['type'] ?? '')), 0, 120),
                'warna' => mb_substr(trim((string) ($group['warna'] ?? '')), 0, 120),
                'rows' => $rows,
            ];

            if (count($groups) >= 20) {
                break;
            }
        }

        return $groups !== [] ? $groups : null;
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
                ? Spreadsheet::readCsvRows($file->getRealPath())
                : Spreadsheet::readXlsxRows($file->getRealPath(), self::IMPORT_MAX_ROWS);
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

        $map = Spreadsheet::mapHeadings(array_shift($rows), self::IMPORT_HEADER_ALIASES);

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

                    // Bila cocok dengan layanan terdaftar, pakai format kanonikalnya.
                    // Bila tidak (mis. nama produk seperti "KAOS", "JERSEY POLA RAGLAN"),
                    // tetap terima apa adanya sebagai jenis pengerjaan/produk agar tidak memblokir impor.
                    $jenis = $matched ?? $jenis;
                }
            } else {
                $jenis = null;
            }

            $deadline = null;
            $rawDeadline = $get('waktu pengambilan');

            if ($rawDeadline !== '') {
                $deadline = Spreadsheet::parseDate($rawDeadline);

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
                [$flag, $flagError] = Spreadsheet::parseFlagOkNok($get($heading));

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
                $total = Spreadsheet::parseAmount($rawTotal);

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
}
