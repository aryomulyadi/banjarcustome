<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use App\Support\ServiceTypes;
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

    public function kebijakanPrivasi(): View
    {
        return view('pages.kebijakan-privasi');
    }

    public function syaratKetentuan(): View
    {
        return view('pages.syarat-ketentuan');
    }
}
