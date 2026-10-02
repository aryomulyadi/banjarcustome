<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PageController extends Controller
{
    public function tentang(): View
    {
        return view('pages.tentang');
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

    public function login(): View
    {
        return view('pages.login');
    }
}
