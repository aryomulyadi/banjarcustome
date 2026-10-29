<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterOption;
use App\Models\TargetData;
use App\Support\Spreadsheet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TargetDataController extends Controller
{
    public function index(Request $request): View
    {
        [$sort, $dir] = $this->parseSort((string) $request->query('sort', 'waktu_pengambilan:asc'));
        $q = trim((string) $request->query('q'));
        $jenis = $request->query('jenis');
        $pesananBahan = $request->query('pesanan_bahan');
        $produksi = $request->query('progres_produksi');
        $finish = $request->query('finish');

        $entries = TargetData::query()
            ->when($q !== '', fn ($query) => $query->search($q))
            ->filterJenis(is_string($jenis) ? $jenis : null)
            ->filterFlag('pesanan_bahan', $this->flagOrNull($pesananBahan))
            ->filterFlag('progres_produksi', $this->flagOrNull($produksi))
            ->filterFlag('finish', $this->flagOrNull($finish))
            ->sort($sort, $dir)
            ->paginate(20)
            ->withQueryString();

        return view('admin.target.index', [
            'entries' => $entries,
            'jenisList' => MasterOption::namesFor(MasterOption::KIND_TARGET_LINE),
            'activeSort' => $sort.':'.$dir,
            'activeJenis' => is_string($jenis) ? $jenis : '',
            'activePesananBahan' => $this->flagOrNull($pesananBahan) ?? '',
            'activeProduksi' => $this->flagOrNull($produksi) ?? '',
            'activeFinish' => $this->flagOrNull($finish) ?? '',
            'search' => $q,
        ]);
    }

    public function create(): View
    {
        return view('admin.target.form', [
            'entry' => null,
            'jenisList' => MasterOption::namesFor(MasterOption::KIND_TARGET_LINE),
            'typeList' => MasterOption::namesFor(MasterOption::KIND_TARGET_TYPE),
            'bahanList' => MasterOption::namesFor(MasterOption::KIND_MATERIAL),
            'warnaList' => MasterOption::namesFor(MasterOption::KIND_COLOR),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entry = TargetData::create($this->validated($request));

        return redirect()
            ->route('admin.target.index')
            ->with('status', 'Data target "'.$entry->customer.'" berhasil ditambahkan.');
    }

    public function edit(TargetData $target): View
    {
        return view('admin.target.form', [
            'entry' => $target,
            'jenisList' => MasterOption::namesFor(MasterOption::KIND_TARGET_LINE),
            'typeList' => MasterOption::namesFor(MasterOption::KIND_TARGET_TYPE),
            'bahanList' => MasterOption::namesFor(MasterOption::KIND_MATERIAL),
            'warnaList' => MasterOption::namesFor(MasterOption::KIND_COLOR),
        ]);
    }

    public function update(Request $request, TargetData $target): RedirectResponse
    {
        $target->update($this->validated($request));

        return redirect()
            ->route('admin.target.index')
            ->with('status', 'Data target "'.$target->customer.'" berhasil diperbarui.');
    }

    public function destroy(TargetData $target): RedirectResponse
    {
        $customer = $target->customer;
        $target->delete();

        return redirect()
            ->route('admin.target.index')
            ->with('status', 'Data target "'.$customer.'" berhasil dihapus.');
    }

    /** "total:desc" -> [sort, dir] dengan fallback aman. */
    private function parseSort(string $value): array
    {
        [$sort, $dir] = array_pad(explode(':', $value, 2), 2, 'asc');

        return [$sort, $dir === 'desc' ? 'desc' : 'asc'];
    }

    private function flagOrNull(mixed $value): ?string
    {
        return in_array($value, [TargetData::OK, TargetData::NOK], true) ? $value : null;
    }

    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'customer' => ['required', 'string', 'max:160'],
            'jenis' => ['nullable', 'string', 'max:80'],
            'waktu_pengambilan' => ['nullable', 'date'],
            'type' => ['nullable', 'string', 'max:80'],
            'bahan' => ['nullable', 'string', 'max:80'],
            'warna' => ['nullable', 'string', 'max:80'],
            'pesanan_bahan' => TargetData::okNokRule(),
            'progres_produksi' => TargetData::okNokRule(),
            'finish' => TargetData::okNokRule(),
            'total' => ['required', 'integer', 'min:0', 'max:1000000000'],
        ], [
            'pesanan_bahan.in' => 'Pesan bahan harus OK atau NOK.',
            'progres_produksi.in' => 'Produksi harus OK atau NOK.',
            'finish.in' => 'Finish harus OK atau NOK.',
            'total.integer' => 'Total harus berupa angka.',
            'total.min' => 'Total tidak boleh negatif.',
        ]);

        if (! empty($validated['waktu_pengambilan'])) {
            $validated['waktu_pengambilan'] = now()->parse($validated['waktu_pengambilan'])->toDateString();
        }

        $validated['total'] = (int) $validated['total'];

        return $validated;
    }

    private const IMPORT_MAX_ROWS = 50000;

    /** @var list<string> */
    private const IMPORT_REQUIRED_HEADERS = ['customer'];

    /**
     * Judul alternatif per kolom kanonik — file asli (template apa pun) bisa
     * langsung diimpor: judul dicocokkan fleksibel via Spreadsheet::mapHeadings.
     */
    private const IMPORT_HEADER_ALIASES = [
        'customer' => ['nama customer', 'pelanggan', 'nama pelanggan'],
        'jenis' => ['jenis produk', 'jenis target', 'line'],
        'waktu pengambilan' => ['pengambilan', 'tanggal ambil', 'tgl ambil', 'waktu ambil', 'deadline'],
        'type' => ['tipe', 'type target'],
        'bahan' => ['material'],
        'warna' => ['color'],
        'pesanan bahan' => ['pesan bahan'],
        'progres produksi' => ['produksi', 'progres'],
        'finish' => ['finishing'],
        'total' => ['jumlah', 'qty', 'pcs', 'total pcs'],
    ];

    /** Judul kolom turunan yang diabaikan saat impor (dihitung sistem). */
    private const IMPORT_IGNORED_HEADINGS = ['no', 'nomor', 'no.', 'id'];

    public function importForm(): View
    {
        $report = session('report');

        if ($report !== null) {
            session()->forget('report');
        }

        return view('admin.target.import', ['report' => $report]);
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
                    : 'File gagal dibaca. Pastikan file .xlsx/.csv tidak rusak dan baris pertama adalah baris judul kolom.',
            ]);
        }

        if ($rows === []) {
            return $this->importRedirect(['fatal' => 'File tidak berisi data.']);
        }

        if (count($rows) > self::IMPORT_MAX_ROWS + 1) {
            return $this->importRedirect(['fatal' => 'File melebihi batas '.self::IMPORT_MAX_ROWS.' baris data.']);
        }

        $headerRow = array_shift($rows);
        $map = Spreadsheet::mapHeadings($headerRow, self::IMPORT_HEADER_ALIASES);

        $missing = array_diff(self::IMPORT_REQUIRED_HEADERS, array_keys($map));

        if ($missing !== []) {
            return $this->importRedirect([
                'fatal' => 'Kolom wajib tidak ditemukan: '.implode(', ', $missing)
                    .'. Judul alternatif diterima (cth. "Pelanggan", "Tgl Ambil") — lihat daftar kolom di halaman ini.',
            ]);
        }

        $warnings = [];

        foreach ($headerRow as $header) {
            if (in_array(Spreadsheet::normalizeHeading((string) $header), self::IMPORT_IGNORED_HEADINGS, true)) {
                $warnings[] = 'Kolom "'.trim((string) $header).'" diabaikan (NO dihitung sistem).';
            }
        }

        $errors = [];
        $records = [];
        $newMaster = [];
        $blankTotal = 0;
        $optionKinds = [
            'jenis' => MasterOption::KIND_TARGET_LINE,
            'type' => MasterOption::KIND_TARGET_TYPE,
            'bahan' => MasterOption::KIND_MATERIAL,
            'warna' => MasterOption::KIND_COLOR,
        ];
        $known = [];

        foreach ($optionKinds as $kind) {
            $known[$kind] = array_map('mb_strtolower', MasterOption::namesFor($kind));
        }

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

            if ($customer === '' || mb_strlen($customer) > 160) {
                $rowErrors[] = 'customer wajib diisi (maks 160 karakter).';
            }

            $waktu = null;
            $rawWaktu = $get('waktu pengambilan');

            if ($rawWaktu !== '') {
                $waktu = Spreadsheet::parseDate($rawWaktu);

                if ($waktu === null) {
                    $rowErrors[] = 'Waktu pengambilan tidak valid (d/m/Y, Y-m-d, atau "24-Oct").';
                }
            }

            $specs = [];

            foreach ($optionKinds as $column => $kind) {
                if ($column === 'jenis') {
                    $max = 60;
                } else {
                    $max = 80;
                }

                $value = $get($column);

                if ($value === '') {
                    $specs[$column] = null;

                    continue;
                }

                if (mb_strlen($value) > $max) {
                    $rowErrors[] = 'Kolom '.$column.' maks '.$max.' karakter.';
                    $specs[$column] = null;

                    continue;
                }

                $specs[$column] = $value;
                $lower = mb_strtolower($value);

                if (! in_array($lower, $known[$kind], true)) {
                    $known[$kind][] = $lower;
                    $newMaster[$kind.'|'.$lower] = ['kind' => $kind, 'name' => $value];
                }
            }

            $flags = [];

            foreach (['pesanan bahan' => 'pesanan_bahan', 'progres produksi' => 'progres_produksi', 'finish' => 'finish'] as $heading => $field) {
                [$ok, $flagError] = Spreadsheet::parseFlagOkNok($get($heading));

                if ($flagError !== null) {
                    $rowErrors[] = 'Kolom "'.$heading.'" '.$flagError;
                }

                $flags[$field] = $ok ? TargetData::OK : TargetData::NOK;
            }

            $total = 0;
            $rawTotal = $get('total');

            if ($rawTotal === '') {
                $blankTotal++;
            } else {
                $total = Spreadsheet::parseAmount($rawTotal);

                if ($total === null || $total < 0) {
                    $rowErrors[] = 'TOTAL tidak valid (angka >= 0, maks Rp100.000.000.000).';
                    $total = 0;
                }
            }

            if ($rowErrors !== []) {
                $errors[] = ['baris' => $line, 'pesan' => implode(' ', $rowErrors)];

                continue;
            }

            $records[] = [
                'customer' => $customer,
                'jenis' => $specs['jenis'],
                'waktu_pengambilan' => $waktu?->toDateString(),
                'type' => $specs['type'],
                'bahan' => $specs['bahan'],
                'warna' => $specs['warna'],
                'pesanan_bahan' => $flags['pesanan_bahan'],
                'progres_produksi' => $flags['progres_produksi'],
                'finish' => $flags['finish'],
                'total' => $total,
            ];
        }

        if ($blankTotal > 0 && $errors === []) {
            $warnings[] = $blankTotal.' baris tanpa TOTAL diisi 0.';
        }

        if ($errors !== []) {
            return $this->importRedirect([
                'errors' => $errors,
                'warnings' => $warnings,
                'imported' => null,
            ]);
        }

        if ($records === []) {
            return $this->importRedirect([
                'fatal' => 'File tidak berisi baris data.',
                'warnings' => $warnings,
            ]);
        }

        $imported = DB::transaction(static function () use ($records, $newMaster) {
            foreach ($newMaster as $option) {
                MasterOption::create($option);
            }

            foreach ($records as $record) {
                TargetData::create($record);
            }

            return count($records);
        });

        return $this->importRedirect([
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
            ->route('admin.target.import')
            ->with('report', $report);
    }
}
