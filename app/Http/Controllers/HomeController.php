<?php

namespace App\Http\Controllers;

use App\Models\Gallery;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $slides = [
            [
                'title' => 'Jasa Konveksi & Sablon Custom Banjarmasin',
                'subtitle' => 'Kaos, jersey, kemeja, jaket, hingga merchandise — produksi rapi, harga bersahabat.',
                'image' => 'images/banner-1.jpg',
            ],
            [
                'title' => 'Bebas Custom Desain',
                'subtitle' => 'Kirim desainmu sendiri atau tim kami bantu dari nol sesuai kebutuhan tim dan komunitas.',
                'image' => 'images/banner-2.jpg',
            ],
            [
                'title' => 'Melayani Ecer & Grosir',
                'subtitle' => 'Dari satuan hingga ratusan pcs. Melayani Banjarmasin dan sekitarnya, kirim ke seluruh Indonesia.',
                'image' => 'images/banner-3.jpg',
            ],
        ];

        $products = Product::with(['colors', 'category'])->latest()->take(8)->get();
        $galleries = Gallery::latest()->take(6)->get();

        $stats = [
            ['value' => '500+', 'label' => 'Pesanan Selesai'],
            ['value' => '100%', 'label' => 'Sablon Anti Pecah'],
            ['value' => '1-7 Hari', 'label' => 'Estimasi Produksi'],
        ];

        return view('home', compact('slides', 'products', 'galleries', 'stats'));
    }
}
