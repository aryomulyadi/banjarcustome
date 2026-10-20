<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Support\ImageOptimizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
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
            'base_price' => $validated['base_price'] ?? null,
            'price_tiers' => $validated['price_tiers'] ?? null,
            'image' => $validated['image'] ?? null,
        ]);

        $this->syncColors($product, $validated['colors'] ?? []);
        $this->syncGallery($product, $request->file('images', []), []);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk "'.$product->title.'" berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.edit', [
            'product' => $product->load(['colors', 'images']),
            'categories' => Category::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $this->validateProduct($request, $product);

        $oldImage = $product->image;

        if ($request->hasFile('image')) {
            $validated['image'] = ImageOptimizer::store($request->file('image'), 'products');
        }

        $removeIds = array_map('intval', $validated['remove_images'] ?? []);
        $newFiles = array_values(array_filter($request->file('images', [])));

        if ($product->images()->count() - count($removeIds) + count($newFiles) > 5) {
            return back()->withErrors(['images' => 'Maksimal 5 foto galeri per produk (hapus sebagian dulu).']);
        }

        $product->update([
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'price_estimate' => $validated['price_estimate'] ?? null,
            'base_price' => $validated['base_price'] ?? null,
            'price_tiers' => $validated['price_tiers'] ?? null,
            'image' => $validated['image'] ?? $product->image,
        ]);

        if (isset($validated['image']) && $oldImage && $oldImage !== $validated['image']) {
            ImageOptimizer::delete($oldImage);
        }

        $this->syncColors($product, $validated['colors'] ?? []);
        $this->syncGallery($product, $newFiles, $removeIds);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk "'.$product->title.'" berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image) {
            ImageOptimizer::delete($product->image);
        }

        foreach ($product->images as $galleryImage) {
            ImageOptimizer::delete($galleryImage->image);
        }

        $product->colors()->delete();
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Produk berhasil dihapus.');
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string', 'max:3000'],
            'price_estimate' => ['nullable', 'string', 'max:100'],
            'base_price' => ['nullable', 'integer', 'min:0', 'max:100000000000'],
            'price_tiers' => [
                'nullable',
                'array',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_array($value)) {
                        return;
                    }

                    $mins = array_filter(array_map(
                        static fn ($tier) => is_array($tier) ? ($tier['min_qty'] ?? null) : null,
                        $value,
                    ));

                    if (count($mins) !== count(array_unique($mins))) {
                        $fail('Tangga jumlah tidak boleh memiliki min qty ganda.');
                    }
                },
            ],
            'price_tiers.*.min_qty' => ['required', 'integer', 'min:1', 'max:1000000'],
            'price_tiers.*.unit_price' => ['required', 'integer', 'min:0', 'max:100000000000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'colors' => ['nullable', 'array'],
            'colors.*.name' => ['required', 'string', 'max:50'],
            'colors.*.hex' => ['required', 'string', 'max:7', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];

        if ($product) {
            $rules['remove_images'] = ['nullable', 'array'];
            $rules['remove_images.*'] = [
                'integer',
                'distinct',
                Rule::exists('product_images', 'id')->where('product_id', $product->id),
            ];
        }

        return $request->validate($rules, [
            'colors.*.hex.regex' => 'Format warna harus berupa heksadesimal seperti #F59E0B.',
            'image.mimes' => 'Format gambar harus jpg, jpeg, png, atau webp.',
            'image.max' => 'Ukuran gambar maksimal 2 MB.',
            'images.array' => 'Maksimal 5 foto galeri per produk.',
            'images.max' => 'Maksimal 5 foto galeri per produk.',
            'images.*.mimes' => 'Format foto galeri harus jpg, jpeg, png, atau webp.',
            'images.*.max' => 'Ukuran tiap foto galeri maksimal 2 MB.',
            'remove_images.*.exists' => 'Foto galeri tidak valid.',
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

    /**
     * @param  array<int, UploadedFile>  $files
     * @param  array<int, int>  $removeIds
     */
    private function syncGallery(Product $product, array $files, array $removeIds): void
    {
        if ($removeIds !== []) {
            foreach ($product->images()->whereIn('id', $removeIds)->get() as $row) {
                ImageOptimizer::delete($row->image);
            }

            $product->images()->whereIn('id', $removeIds)->delete();
        }

        $next = (int) ($product->images()->max('sort') ?? -1) + 1;

        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $product->images()->create([
                'image' => ImageOptimizer::store($file, 'products'),
                'sort' => $next++,
                'alt' => $product->title,
            ]);
        }
    }
}
