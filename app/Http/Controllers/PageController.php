<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Models\Product;
use App\Support\JerseyCatalog;
use App\Support\ServiceTypes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class PageController extends Controller
{
    public function tentang(): View
    {
        return view('pages.tentang');
    }

    public function layanan(): View
    {
        return view('pages.layanan', [
            'services' => ServiceTypes::items(),
        ]);
    }

    public function lokasi(): View
    {
        return view('pages.lokasi');
    }

    public function faq(): View
    {
        return view('pages.faq', [
            'faqs' => Faq::active()->get(),
        ]);
    }

    public function sizeChart(): View
    {
        return view('pages.size-chart');
    }

    public function katalogJersey(): View
    {
        return view('pages.katalog-jersey', [
            'jerseyCollars' => JerseyCatalog::collars(),
            'jerseyFabrics' => JerseyCatalog::fabrics(),
            'jerseyProducts' => Product::with(['colors', 'category'])
                ->whereHas('category', fn (Builder $query) => $query->where(function (Builder $q) {
                    $q->where('name', 'like', '%jersey%')->orWhere('slug', 'like', '%jersey%');
                }))
                ->latest()
                ->limit(8)
                ->get(),
        ]);
    }

    public function kebijakanPrivasi(): View
    {
        return view('pages.kebijakan-privasi');
    }

    public function syaratKetentuan(): View
    {
        return view('pages.syarat-ketentuan');
    }
}
