<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Support\HomeContent;
use App\Support\ServiceTypes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'slides' => HomeContent::slides(),
            'stats' => HomeContent::stats(),
            'services' => ServiceTypes::items(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'slides' => ['required', 'array', 'size:3'],
            'slides.*.title' => ['required', 'string', 'max:120'],
            'slides.*.subtitle' => ['required', 'string', 'max:255'],
            'slides.*.image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'stats' => ['required', 'array', 'size:3'],
            'stats.*.value' => ['required', 'string', 'max:30'],
            'stats.*.label' => ['required', 'string', 'max:60'],
            'services' => ['required', 'array', 'min:1', 'max:30'],
            'services.*.title' => ['required', 'string', 'max:60'],
            'services.*.desc' => ['nullable', 'string', 'max:255'],
        ], [
            'slides.*.image.mimes' => 'Format banner harus jpg, jpeg, png, atau webp.',
            'slides.*.image.max' => 'Ukuran banner maksimal 3 MB.',
            'services.required' => 'Minimal satu jenis layanan harus tersedia.',
            'services.min' => 'Minimal satu jenis layanan harus tersedia.',
            'services.*.title.required' => 'Judul layanan wajib diisi.',
            'services.*.title.max' => 'Judul layanan maksimal 60 karakter.',
        ]);

        $slides = collect($validated['slides'])->map(function (array $slide, int $index) use ($request) {
            $stored = $this->storedImage($request, $index, $slide['image'] ?? null);

            return [
                'title' => $slide['title'],
                'subtitle' => $slide['subtitle'],
                'image' => $stored,
            ];
        })->values()->all();

        Setting::set('home.slides', json_encode($slides));
        Setting::set('home.stats', json_encode($validated['stats']));

        ServiceTypes::setItems(array_map(fn (array $item) => [
            'title' => $item['title'],
            'desc' => $item['desc'] ?? '',
        ], $validated['services']));

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Pengaturan berhasil diperbarui.');
    }

    private function storedImage(Request $request, int $index, ?string $current): ?string
    {
        $key = "slides.{$index}.image";

        if (! $request->hasFile($key)) {
            return $current;
        }

        $path = $request->file($key)->store('slides', 'public');

        if ($current && Storage::disk('public')->exists($current)) {
            Storage::disk('public')->delete($current);
        }

        return $path;
    }
}
