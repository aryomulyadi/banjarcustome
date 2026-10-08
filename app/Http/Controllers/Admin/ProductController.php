<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = Product::with(['category', 'colors'])->latest()->orderByDesc('id');

        if ($request->filled('q')) {
            $search = $request->string('q')->trim();

            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhereHas('category', fn ($b) => $b->where('name', 'like', "%{$search}%"));
            });
        }

        return view('admin.products.index', [
            'products' => $query->paginate(10)->withQueryString(),
            'search' => $request->query('q', ''),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        if ($request->hasFile('image')) {
            $validated['image'] = ImageOptimizer::store($request->file('image'), 'products');
        }

        $product = Product::create([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'slug' => $this->uniqueSlug($validated['title']),
            'description' => $validated['description'] ?? null,
            'price_estimate' => $validated['price_estimate'] ?? null,
            'image' => $validated['image'] ?? null,
        ]);

        $this->syncColors($product, $validated['colors'] ?? []);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk "'.$product->title.'" berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load('colors'),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request);

        $oldImage = $product->image;

        if ($request->hasFile('image')) {
            $validated['image'] = ImageOptimizer::store($request->file('image'), 'products');
        }

        $product->update([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price_estimate' => $validated['price_estimate'] ?? null,
            'image' => $validated['image'] ?? $product->image,
        ]);

        if (isset($validated['image']) && $oldImage && $oldImage !== $validated['image']) {
            ImageOptimizer::delete($oldImage);
        }

        $this->syncColors($product, $validated['colors'] ?? []);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk "'.$product->title.'" berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            ImageOptimizer::delete($product->image);
        }

        $product->colors()->delete();
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil dihapus.');
    }

    private function validateProduct(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:3000'],
            'price_estimate' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'colors' => ['nullable', 'array'],
            'colors.*.name' => ['required', 'string', 'max:50'],
            'colors.*.hex' => ['required', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ], [
            'colors.*.hex.regex' => 'Format warna harus berupa heksadesimal seperti #F59E0B.',
            'image.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'produk';
        $slug = $base;
        $suffix = 2;

        while (Product::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }

    private function syncColors(Product $product, array $colors): void
    {
        $product->colors()->delete();

        foreach ($colors as $color) {
            $product->colors()->create([
                'name' => $color['name'],
                'hex' => $color['hex'],
            ]);
        }
    }
}
