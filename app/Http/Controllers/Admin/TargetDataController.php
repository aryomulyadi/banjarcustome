<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterOption;
use App\Models\TargetData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
