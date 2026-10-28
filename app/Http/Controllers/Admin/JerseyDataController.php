<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JerseyData;
use App\Models\MasterOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class JerseyDataController extends Controller
{
    public function index(Request $request): View
    {
        [$sort, $dir] = $this->parseSort((string) $request->query('sort', 'tanggal_masuk:desc'));
        $q = trim((string) $request->query('q'));
        $desainer = $request->query('desainer');
        $status = $request->query('status');

        $entries = JerseyData::query()
            ->when($q !== '', fn ($query) => $query->search($q))
            ->filterDesainer(is_string($desainer) ? $desainer : null)
            ->filterStatus(is_string($status) ? $status : null)
            ->sort($sort, $dir)
            ->paginate(20)
            ->withQueryString();

        return view('admin.jersey.index', [
            'entries' => $entries,
            'desainers' => MasterOption::namesFor(MasterOption::KIND_DESIGNER),
            'statuses' => JerseyData::STATUSES,
            'activeSort' => $sort.':'.$dir,
            'activeDesainer' => is_string($desainer) ? $desainer : '',
            'activeStatus' => is_string($status) ? $status : '',
            'search' => $q,
        ]);
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
}
