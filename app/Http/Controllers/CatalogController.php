<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderCatalog($request, null);
    }

    public function category(Request $request, string $slug): View
    {
        $category = Category::where('slug', $slug)->firstOrFail();

        return $this->renderCatalog($request, $category);
    }

    public function show(string $slug): View
    {
        $product = Product::with(['colors', 'category'])
            ->where('slug', $slug)
            ->firstOrFail();

        $related = Product::with(['colors', 'category'])
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->latest()->orderByDesc('id')
            ->take(4)
            ->get();

        return view('catalog.show', compact('product', 'related'));
    }

    private function renderCatalog(Request $request, ?Category $activeCategory): View
    {
        $query = Product::with(['colors', 'category'])->latest()->orderByDesc('id');

        if ($activeCategory) {
            $query->where('category_id', $activeCategory->id);
        } elseif ($request->filled('kategori')) {
            $category = Category::where('slug', $request->string('kategori'))->first();
            if ($category) {
                $activeCategory = $category;
                $query->where('category_id', $category->id);
            }
        }

        if ($request->filled('q')) {
            $search = $request->string('q');
            $query->where(function ($builder) use ($search) {
                $builder->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('warna')) {
            $color = $request->string('warna');
            $query->whereHas('colors', fn ($builder) => $builder->where('name', $color));
        }

        $products = $query->paginate(9)->withQueryString();

        $categories = Category::withCount('products')->orderBy('name')->get();

        $colors = ProductColor::select('name', 'hex')
            ->distinct()
            ->orderBy('name')
            ->get();

        return view('catalog.index', compact('products', 'categories', 'colors', 'activeCategory'));
    }
}
