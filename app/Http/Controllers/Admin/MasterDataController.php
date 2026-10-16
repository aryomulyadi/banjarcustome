<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterOption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterDataController extends Controller
{
    public function index(): View
    {
        return view('admin.master.index', [
            'grouped' => collect(MasterOption::KINDS)
                ->mapWithKeys(fn (string $kind) => [
                    $kind => MasterOption::query()->forKind($kind)->get(),
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kind' => ['required', Rule::in(MasterOption::KINDS)],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('options', 'name')->where(fn ($query) => $query->where('kind', $request->input('kind'))),
            ],
            'hex' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ], [
            'kind.in' => 'Jenis data master tidak dikenal.',
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 120 karakter.',
            'name.unique' => 'Nama tersebut sudah terdaftar pada data master ini.',
            'hex.regex' => 'Format warna harus berupa heksadesimal, contoh #a3e635.',
        ]);

        $option = MasterOption::create([
            'kind' => $validated['kind'],
            'name' => $validated['name'],
            'hex' => $validated['hex'] ?? null,
        ]);

        return redirect()
            ->route('admin.master.index')
            ->with('status', 'Data "'.$option->name.'" berhasil ditambahkan.');
    }

    public function destroy(MasterOption $option): RedirectResponse
    {
        $name = $option->name;
        $option->delete();

        return redirect()
            ->route('admin.master.index')
            ->with('status', 'Data "'.$name.'" berhasil dihapus.');
    }
}
