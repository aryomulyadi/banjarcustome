<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use App\Support\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TestimonialController extends Controller
{
    public function index(): View
    {
        return view('admin.testimonials.index', [
            'testimonials' => Testimonial::latest()->orderByDesc('id')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.testimonials.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateTestimonial($request);

        if ($request->hasFile('photo')) {
            $validated['photo'] = ImageOptimizer::store($request->file('photo'), 'testimonials');
        }

        Testimonial::create($validated);

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', 'Testimoni berhasil ditambahkan.');
    }

    public function edit(Testimonial $testimonial): View
    {
        return view('admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial): RedirectResponse
    {
        $validated = $this->validateTestimonial($request);

        if ($request->hasFile('photo')) {
            $oldPhoto = $testimonial->photo;
            $validated['photo'] = ImageOptimizer::store($request->file('photo'), 'testimonials');

            if ($oldPhoto) {
                ImageOptimizer::delete($oldPhoto);
            }
        }

        $testimonial->update($validated);

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', 'Testimoni berhasil diperbarui.');
    }

    public function destroy(Testimonial $testimonial): RedirectResponse
    {
        if ($testimonial->photo) {
            ImageOptimizer::delete($testimonial->photo);
        }

        $testimonial->delete();

        return redirect()
            ->route('admin.testimonials.index')
            ->with('status', 'Testimoni berhasil dihapus.');
    }

    private function validateTestimonial(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['required', 'string', 'max:1000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'photo.mimes' => 'Format foto harus jpg, jpeg, png, atau webp.',
            'photo.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
