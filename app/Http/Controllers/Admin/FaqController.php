<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FaqController extends Controller
{
    public function index(): View
    {
        return view('admin.faqs.index', [
            'faqs' => Faq::orderBy('position')->orderBy('id')->paginate(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.faqs.create', [
            'nextPosition' => (int) Faq::max('position') + 1,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateFaq($request);

        Faq::create($validated);

        return redirect()
            ->route('admin.faqs.index')
            ->with('status', 'FAQ berhasil ditambahkan.');
    }

    public function edit(Faq $faq): View
    {
        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq): RedirectResponse
    {
        $faq->update($this->validateFaq($request));

        return redirect()
            ->route('admin.faqs.index')
            ->with('status', 'FAQ berhasil diperbarui.');
    }

    public function destroy(Faq $faq): RedirectResponse
    {
        $faq->delete();

        return redirect()
            ->route('admin.faqs.index')
            ->with('status', 'FAQ berhasil dihapus.');
    }

    private function validateFaq(Request $request): array
    {
        $validated = $request->validate([
            'question' => ['required', 'string', 'max:255'],
            'answer' => ['required', 'string', 'max:3000'],
            'position' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        return $validated;
    }
}
