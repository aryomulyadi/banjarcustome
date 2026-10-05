<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GalleryController extends Controller
{
    public function index(Request $request): View
    {
        $query = Gallery::latest()->orderByDesc('id');

        $activeCategory = null;

        if ($request->filled('kategori')) {
            $activeCategory = $request->string('kategori');
            $query->where('category', $activeCategory);
        }

        $galleries = $query->paginate(12)->withQueryString();

        $categories = Gallery::select('category')
            ->distinct()
            ->whereNotNull('category')
            ->orderBy('category')
            ->pluck('category');

        return view('galeri', compact('galleries', 'categories', 'activeCategory'));
    }
}
