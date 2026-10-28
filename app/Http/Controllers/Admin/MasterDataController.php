<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MasterOption;
use App\Support\ImageOptimizer;
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
        $kind = $request->input('kind');
        $isJersey = is_string($kind) && MasterOption::isJerseyKind($kind);

        $rules = [
            'kind' => ['required', Rule::in(MasterOption::KINDS)],
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('options', 'name')->where(fn ($query) => $query->where('kind', $kind)),
            ],
            'hex' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];

        $messages = [
            'kind.in' => 'Jenis data master tidak dikenal.',
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 120 karakter.',
            'name.unique' => 'Nama tersebut sudah terdaftar pada data master ini.',
            'hex.regex' => 'Format warna harus berupa heksadesimal, contoh #a3e635.',
        ];

        if ($isJersey) {
            $rules['fee'] = ['nullable', 'integer', 'min:0', 'max:1000000000'];
            $rules['image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];

            $messages['fee.integer'] = 'Biaya upgrade harus berupa angka.';
            $messages['fee.min'] = 'Biaya upgrade tidak boleh negatif.';
            $messages['image.image'] = 'Foto harus berupa gambar.';
            $messages['image.mimes'] = 'Format gambar harus jpg, jpeg, png, atau webp.';
            $messages['image.max'] = 'Ukuran gambar maksimal 2 MB.';
        }

        $validated = $request->validate($rules, $messages);

        $data = [
            'kind' => $validated['kind'],
            'name' => $validated['name'],
            'hex' => $validated['hex'] ?? null,
        ];

        if ($isJersey) {
            $data['fee'] = (int) ($validated['fee'] ?? 0);
            $data['active'] = true;
            $data['sort'] = 0;
            $data['image'] = $request->hasFile('image')
                ? ImageOptimizer::store($request->file('image'), 'jersey-options')
                : null;
        }

        $option = MasterOption::create($data);

        return redirect()
            ->route('admin.master.index')
            ->with('status', 'Data "'.$option->name.'" berhasil ditambahkan.');
    }

    public function edit(MasterOption $option): View
    {
        return view('admin.master.edit', [
            'option' => $option,
        ]);
    }

    public function update(Request $request, MasterOption $option): RedirectResponse
    {
        $isJersey = MasterOption::isJerseyKind($option->kind);

        $rules = [
            'name' => [
                'required',
                'string',
                'max:120',
                Rule::unique('options', 'name')
                    ->where(fn ($query) => $query->where('kind', $option->kind))
                    ->ignore($option->id),
            ],
        ];

        $messages = [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 120 karakter.',
            'name.unique' => 'Nama tersebut sudah terdaftar pada data master ini.',
        ];

        if ($isJersey) {
            $rules['fee'] = ['required', 'integer', 'min:0', 'max:1000000000'];
            $rules['image'] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'];
            $rules['active'] = ['nullable', 'boolean'];
            $rules['sort'] = ['nullable', 'integer', 'min:0', 'max:1000000'];

            $messages['fee.required'] = 'Biaya upgrade wajib diisi.';
            $messages['fee.integer'] = 'Biaya upgrade harus berupa angka.';
            $messages['fee.min'] = 'Biaya upgrade tidak boleh negatif.';
            $messages['image.image'] = 'Foto harus berupa gambar.';
            $messages['image.mimes'] = 'Format gambar harus jpg, jpeg, png, atau webp.';
            $messages['image.max'] = 'Ukuran gambar maksimal 2 MB.';
            $messages['sort.min'] = 'Urutan tidak boleh negatif.';
        }

        if ($option->kind === MasterOption::KIND_COLOR) {
            $rules['hex'] = ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'];
            $messages['hex.regex'] = 'Format warna harus berupa heksadesimal, contoh #a3e635.';
        }

        $validated = $request->validate($rules, $messages);

        $data = ['name' => $validated['name']];

        if ($option->kind === MasterOption::KIND_COLOR) {
            $data['hex'] = $validated['hex'] ?? null;
        }

        $oldImage = $option->image;

        if ($isJersey) {
            $data['fee'] = (int) $validated['fee'];
            $data['active'] = $request->boolean('active');
            $data['sort'] = (int) ($validated['sort'] ?? 0);
            $data['image'] = $oldImage;

            if ($request->hasFile('image')) {
                $data['image'] = ImageOptimizer::store($request->file('image'), 'jersey-options');
            }
        }

        $option->update($data);

        if ($isJersey && $data['image'] !== $oldImage) {
            ImageOptimizer::delete($oldImage);
        }

        return redirect()
            ->route('admin.master.index')
            ->with('status', 'Opsi "'.$option->name.'" berhasil diperbarui.');
    }

    public function destroy(MasterOption $option): RedirectResponse
    {
        $name = $option->name;
        ImageOptimizer::delete($option->image);
        $option->delete();

        return redirect()
            ->route('admin.master.index')
            ->with('status', 'Data "'.$name.'" berhasil dihapus.');
    }
}
