<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Product;
use App\Models\Testimonial;
use App\Support\HomeContent;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $slides = HomeContent::slides();
        $stats = HomeContent::stats();

        $products = Cache::remember('home.products', now()->addMinutes(10), fn () => Product::with(['colors', 'category'])->latest()->orderByDesc('id')->take(8)->get());
        $galleries = Cache::remember('home.galleries', now()->addMinutes(10), fn () => Gallery::latest()->orderByDesc('id')->take(6)->get());
        $testimonials = Cache::remember('home.testimonials', now()->addMinutes(10), fn () => Testimonial::active()->take(6)->get());

        return view('home', compact('slides', 'products', 'galleries', 'stats', 'testimonials'));
    }
}
