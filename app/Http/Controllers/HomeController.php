<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Product;
use App\Models\Testimonial;
use App\Support\HomeContent;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $slides = HomeContent::slides();
        $stats = HomeContent::stats();

        $products = Product::with(['colors', 'category'])->latest()->orderByDesc('id')->take(8)->get();
        $galleries = Gallery::latest()->orderByDesc('id')->take(6)->get();
        $testimonials = Testimonial::active()->take(6)->get();

        return view('home', compact('slides', 'products', 'galleries', 'stats', 'testimonials'));
    }
}
