<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JerseyData;
use App\Models\MasterOption;
use App\Support\Spreadsheet;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JerseyDataController extends Controller
{
    public function index(Request $request): View
    {
        [$sort, $dir] = $this->parseSort((string) $request->query('sort', 'tanggal_masuk:desc'));
        $q = trim((string) $request->query('q'));
        $desainer = $request->query('desainer');
        $activeDesainer = is_string($desainer) ? $desainer : '';

        // Filter multi-status ala spreadsheet: status=Bogus (string tunggal
        // di luar whitelist) diabaikan agar konsisten dgn status[].
        $rawStatus = $request->query('status');
        $activeStatuses = match (true) {
            is_array($rawStatus) => array_values(array_filter(
                $rawStatus,
                static fn ($value) => is_string($value) && in_array($value, JerseyData::STATUSES, true)
            )),
            is_string($rawStatus) && in_array($rawStatus, JerseyData::STATUSES, true) => [$rawStatus],
            default => [],
        };

        $base = JerseyData::query()
            ->when($q !== '', fn ($query) => $query->search($q))
            ->filterDesainer($activeDesainer !== '' ? $activeDesainer : null);

        $entries = (clone $base)
            ->filterStatus($activeStatuses)
            ->sort($sort, $dir)
            ->paginate(20)
            ->withQueryString();

        // Hitungan per status dgn filter q/desainer aktif (tanpa filter status),
        // agar checklist dropdown menunjukkan total realistis tiap opsi.
        $statusCounts = [];

        foreach (JerseyData::STATUSES as $statusOption) {
            $statusCounts[$statusOption] = (clone $base)->where('status', $statusOption)->count();
        }

        return view('admin.jersey.index', [
            'entries' => $entries,
            'desainers' => MasterOption::namesFor(MasterOption::KIND_DESIGNER),
            'statuses' => JerseyData::STATUSES,
            'activeSort' => $sort.':'.$dir,
            'activeDesainer' => $activeDesainer,
            'activeStatuses' => $activeStatuses,
            'statusCounts' => $statusCounts,
            'search' => $q,
        ]);
    }

    public function updateStatus(Request $request, JerseyData $jersey): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(JerseyData::STATUSES)],
        ], [
            'status.in' => 'Status tidak dikenal.',
        ]);

        $jersey->update(['status' => $validated['status']]);

        if ($request->wantsJson()) {
            return response()->json(['status' => $jersey->status]);
        }

        return redirect()
            ->route('admin.jersey.index')
            ->with('status', 'Status "'.$jersey->customer.'" diperbarui menjadi "'.$jersey->status.'".');
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', Rule::exists('jersey_data', 'id')],
            'status' => ['required', 'string', Rule::in(JerseyData::STATUSES)],
        ], [
            'ids.required' => 'Pilih minimal satu data untuk diubah.',
            'ids.*.exists' => 'Ada data yang tidak ditemukan.',
            'status.in' => 'Status tidak dikenal.',
        ]);

        $count = DB::transaction(static function () use ($validated): int {
            return JerseyData::whereIn('id', $validated['ids'])->update(['status' => $validated['status']]);
        });

        return redirect()
            ->back()
            ->with('status', $count.' data diubah statusnya menjadi "'.$validated['status'].'".');
    }

    public function create(): View
    {
        return view('admin.jersey.form', [
            'entry' => null,
            'desainers' => MasterOption::namesFor(MasterOption::KIND_DESIGNER),
            'collars' => MasterOption::namesFor(MasterOption::KIND_COLLAR),
            'fabrics' => MasterOption::namesFor(MasterOption::KIND_FABRIC),
            'models' => MasterOption::namesFor(MasterOption::KIND_JERSEY_MODEL),
            'statuses' => JerseyData::STATUSES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $entry = JerseyData::create($this->validated($request));

        return redirect()
            ->route('admin.jersey.index')
            ->with('status', 'Data jersey "'.$entry->customer.'" berhasil ditambahkan.');
    }

    public function edit(JerseyData $jersey): View
    {
        return view('admin.jersey.form', [
            'entry' => $jersey,
            'desainers' => MasterOption::namesFor(MasterOption::KIND_DESIGNER),
            'collars' => MasterOption::namesFor(MasterOption::KIND_COLLAR),
            'fabrics' => MasterOption::namesFor(MasterOption::KIND_FABRIC),
            'models' => MasterOption::namesFor(MasterOption::KIND_JERSEY_MODEL),
            'statuses' => JerseyData::STATUSES,
        ]);
    }

    public function update(Request $request, JerseyData $jersey): RedirectResponse
    {
        $jersey->update($this->validated($request));

        return redirect()
            ->route('admin.jersey.index')
            ->with('status', 'Data jersey "'.$jersey->customer.'" berhasil diperbarui.');
    }

    public function destroy(JerseyData $jersey): RedirectResponse
    {
        $customer = $jersey->customer;
        $jersey->delete();

        return redirect()
            ->route('admin.jersey.index')
            ->with('status', 'Data jersey "'.$customer.'" berhasil dihapus.');
    }

    /** "deadline:desc" -> [sort, dir] dengan fallback aman. */
    private function parseSort(string $value): array
    {
        [$sort, $dir] = array_pad(explode(':', $value, 2), 2, 'asc');

        return [$sort, $dir === 'desc' ? 'desc' : 'asc'];
    }

    private function validated(Request $request): array
    {
        $desainers = MasterOption::namesFor(MasterOption::KIND_DESIGNER);

        $validated = $request->validate([
            'tanggal_masuk' => ['required', 'date'],
            'desainer' => ['required', 'string', 'max:60', Rule::in($desainers)],
            'customer' => ['required', 'string', 'max:160'],
            'deadline' => ['nullable', 'date', 'after_or_equal:tanggal_masuk'],
            'jenis_kerah' => ['nullable', 'string', 'max:80'],
            'jenis_bahan' => ['nullable', 'string', 'max:80'],
            'model' => ['nullable', 'string', 'max:80'],
            'status' => ['required', 'string', Rule::in(JerseyData::STATUSES)],
            'catatan' => ['nullable', 'string', 'max:2000'],
        ], [
            'desainer.in' => 'Desainer harus memilih salah satu dari data master.',
            'status.in' => 'Status tidak dikenal.',
            'deadline.after_or_equal' => 'Deadline tidak boleh sebelum tanggal masuk.',
        ]);

        $validated['tanggal_masuk'] = now()->parse($validated['tanggal_masuk'])->toDateString();
        if (! empty($validated['deadline'])) {
            $validated['deadline'] = now()->parse($validated['deadline'])->toDateString();
        }

        return $validated;
    }

    private const IMPORT_MAX_ROWS = 50000;

    /** @var list<string> */
    private const IMPORT_REQUIRED_HEADERS = ['tanggal masuk', 'customer', 'desainer'];

    /**
     * Judul alternatif per kolom kanonik — file asli (template apa pun) bisa
     * langsung diimpor: judul dicocokkan fleksibel via Spreadsheet::mapHeadings.
     */
    private const IMPORT_HEADER_ALIASES = [
        'tanggal masuk' => ['tgl masuk', 'tanggal', 'tgl', 'date'],
        'desainer' => ['designer'],
        'customer' => ['nama customer', 'nama/no. customer', 'pelanggan', 'nama pelanggan'],
        'deadline' => ['waktu pengambilan', 'batas waktu', 'due date', 'due'],
        'jenis kerah' => ['kerah'],
        'jenis bahan' => ['bahan'],
        'model' => ['jenis model', 'model jersey'],
        'status' => ['status jersey'],
        'catatan' => ['note', 'notes', 'keterangan'],
    ];

    /** Judul kolom turunan yang diabaikan saat impor (dihitung sistem). */
    private const IMPORT_IGNORED_HEADINGS = ['no', 'nomor', 'no.', 'id', 'sisa hari', 'hari'];

    /**
     * Status bahasa sehari-hari di file lama dipetakan ke status resmi
     * (pencocokan dilakukan setelah case-insensitive match ke STATUSES gagal).
     */
    private const IMPORT_STATUS_ALIASES = [
        'pending' => JerseyData::STATUS_PROSES_DESAIN,
        'belum mulai' => JerseyData::STATUS_PROSES_DESAIN,
        'proses' => JerseyData::STATUS_PROSES_DESAIN,
        'masuk printing' => 'Printing',
        'selesai' => 'Done',
        'batal' => 'Cancel',
    ];

    public function importForm(): View
    {
        $report = session('report');

        if ($report !== null) {
            session()->forget('report');
        }

        return view('admin.jersey.import', ['report' => $report]);
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
                    .'. Judul alternatif diterima (cth. "TGL MASUK", "Nama/No. Customer", "Designer") — lihat daftar kolom di halaman ini.',
            ]);
        }

        $warnings = [];

        foreach ($headerRow as $header) {
            if (in_array(Spreadsheet::normalizeHeading((string) $header), self::IMPORT_IGNORED_HEADINGS, true)) {
                $warnings[] = 'Kolom "'.trim((string) $header).'" diabaikan (NO & sisa hari dihitung sistem).';
            }
        }

        $errors = [];
        $records = [];
        $newMaster = [];
        $inherited = [];
        $optionKinds = [
            'desainer' => MasterOption::KIND_DESIGNER,
            'jenis kerah' => MasterOption::KIND_COLLAR,
            'jenis bahan' => MasterOption::KIND_FABRIC,
            'model' => MasterOption::KIND_JERSEY_MODEL,
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

            // File nyata memakai pola baris lanjutan: kolom wajib dikosongkan
            // saat order sama berlanjut dengan varian berbeda — warisi dari
            // baris data terakhir yang terisi (baris pertama tetap wajib).
            $tanggalMasuk = Spreadsheet::parseDate($get('tanggal masuk'));

            if ($tanggalMasuk === null && $get('tanggal masuk') === '') {
                $tanggalMasuk = $inherited['tanggal masuk'] ?? null;

                if ($tanggalMasuk !== null) {
                    $warnings[] = 'Baris '.$line.': tanggal masuk diwariskan dari baris sebelumnya.';
                }
            }

            if ($tanggalMasuk === null) {
                $rowErrors[] = 'Tanggal masuk wajib diisi (d/m/Y atau Y-m-d).';
            }

            $customer = $get('customer');

            if ($customer === '') {
                $customer = $inherited['customer'] ?? '';

                if ($customer !== '') {
                    $warnings[] = 'Baris '.$line.': customer diwariskan dari baris sebelumnya ('.$customer.').';
                }
            }

            if ($customer === '' || mb_strlen($customer) > 160) {
                $rowErrors[] = 'customer wajib diisi (maks 160 karakter).';
            }

            $desainer = $get('desainer');

            if ($desainer === '') {
                $desainer = $inherited['desainer'] ?? '';

                if ($desainer !== '') {
                    $warnings[] = 'Baris '.$line.': desainer diwariskan dari baris sebelumnya ('.$desainer.').';
                }
            }

            if ($desainer === '' || mb_strlen($desainer) > 60) {
                $rowErrors[] = 'Desainer wajib diisi (maks 60 karakter).';
                $desainer = null;
            } else {
                $kind = $optionKinds['desainer'];
                $lower = mb_strtolower($desainer);

                if (! in_array($lower, $known[$kind], true)) {
                    $known[$kind][] = $lower;
                    $newMaster[$kind.'|'.$lower] = ['kind' => $kind, 'name' => $desainer];
                }
            }

            $deadline = null;
            $rawDeadline = $get('deadline');

            if ($rawDeadline !== '') {
                $deadline = Spreadsheet::parseDate($rawDeadline);

                if ($deadline === null) {
                    // File nyata memuat teks tujuan ("DESAIN ONLY") di kolom
                    // deadline — bukan tanggal. Lepas tanpa tanggal + warning.
                    $deadline = null;
                    $warnings[] = 'Baris '.$line.': deadline "'.$rawDeadline.'" bukan tanggal — diabaikan.';
                } elseif ($tanggalMasuk !== null && $deadline->lt($tanggalMasuk->startOfDay())) {
                    $rowErrors[] = 'Deadline tidak boleh sebelum tanggal masuk.';
                }
            }

            $specs = ['jenis kerah' => null, 'jenis bahan' => null, 'model' => null];

            foreach ($specs as $column => $_) {
                $value = $get($column);

                if ($value === '') {
                    continue;
                }

                if (mb_strlen($value) > 80) {
                    $rowErrors[] = 'Kolom '.$column.' maks 80 karakter.';

                    continue;
                }

                $specs[$column] = $value;
                $kind = $optionKinds[$column];
                $lower = mb_strtolower($value);

                if (! in_array($lower, $known[$kind], true)) {
                    $known[$kind][] = $lower;
                    $newMaster[$kind.'|'.$lower] = ['kind' => $kind, 'name' => $value];
                }
            }

            $status = JerseyData::STATUS_PROSES_DESAIN;
            $rawStatus = $get('status');

            if ($rawStatus !== '') {
                $matched = null;
                $lowerStatus = mb_strtolower($rawStatus);

                foreach (JerseyData::STATUSES as $option) {
                    if (mb_strtolower($option) === $lowerStatus) {
                        $matched = $option;
                        break;
                    }
                }

                if ($matched === null) {
                    $matched = self::IMPORT_STATUS_ALIASES[$lowerStatus] ?? null;
                }

                if ($matched === null) {
                    $rowErrors[] = 'Status "'.$rawStatus.'" tidak dikenal (pakai: '.implode(', ', JerseyData::STATUSES).').';
                } else {
                    $status = $matched;
                }
            }

            $catatan = $get('catatan');

            if (mb_strlen($catatan) > 2000) {
                $rowErrors[] = 'Catatan maks 2000 karakter.';
            }

            if ($rowErrors !== []) {
                $errors[] = ['baris' => $line, 'pesan' => implode(' ', $rowErrors)];

                continue;
            }

            $records[] = [
                'tanggal_masuk' => $tanggalMasuk->toDateString(),
                'desainer' => $desainer,
                'customer' => $customer,
                'deadline' => $deadline?->toDateString(),
                'jenis_kerah' => $specs['jenis kerah'],
                'jenis_bahan' => $specs['jenis bahan'],
                'model' => $specs['model'],
                'status' => $status,
                'catatan' => $catatan !== '' ? $catatan : null,
            ];

            // Baris lanjutan berikutnya mewarisi kolom kosong dari baris ini.
            $inherited = [
                'tanggal masuk' => $tanggalMasuk,
                'customer' => $customer,
                'desainer' => $desainer,
            ];
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
                JerseyData::create($record);
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
            ->route('admin.jersey.import')
            ->with('report', $report);
    }
}
