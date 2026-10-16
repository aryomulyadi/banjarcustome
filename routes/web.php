<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TrackOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/produk', [CatalogController::class, 'index'])->name('produk.index');
Route::get('/kategori/{slug}', [CatalogController::class, 'category'])->name('kategori');
Route::get('/produk/{slug}', [CatalogController::class, 'show'])->name('produk.show');

Route::get('/galeri', [GalleryController::class, 'index'])->name('galeri');

Route::get('/pesan', [OrderController::class, 'create'])->name('pesan.create');
Route::post('/pesan', [OrderController::class, 'store'])->middleware('throttle:12,1')->name('pesan.store');
Route::get('/pesan/sukses/{token}', [OrderController::class, 'success'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('pesan.success');

Route::get('/cek-pesanan', [TrackOrderController::class, 'create'])->name('track.create');
Route::post('/cek-pesanan', [TrackOrderController::class, 'store'])->middleware('throttle:10,1')->name('track.store');

Route::get('/lacak/{token}', [TrackOrderController::class, 'token'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('track.token');
Route::get('/lacak/{token}/desain', [TrackOrderController::class, 'downloadDesign'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->name('track.design');
Route::post('/lacak/{token}/desain/setujui', [TrackOrderController::class, 'approve'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->middleware('throttle:10,1')
    ->name('track.approve');
Route::post('/lacak/{token}/desain/revisi', [TrackOrderController::class, 'revision'])
    ->where('token', '[A-Za-z0-9]{40}')
    ->middleware('throttle:10,1')
    ->name('track.revision');

Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:6,1')->name('login.store');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

Route::get('/tentang', [PageController::class, 'tentang'])->name('tentang');
Route::get('/layanan', [PageController::class, 'layanan'])->name('layanan');
Route::get('/lokasi', [PageController::class, 'lokasi'])->name('lokasi');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/panduan-ukuran', [PageController::class, 'sizeChart'])->name('size-chart');
Route::get('/panduan-jersey', [PageController::class, 'panduanJersey'])->name('panduan-jersey');
Route::get('/kebijakan-privasi', [PageController::class, 'kebijakanPrivasi'])->name('kebijakan-privasi');
Route::get('/syarat-ketentuan', [PageController::class, 'syaratKetentuan'])->name('syarat-ketentuan');

Route::get('/sitemap.xml', [SitemapController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SitemapController::class, 'robots'])->name('robots');

require __DIR__.'/admin.php';

Route::fallback(function () {
    return response()->view('errors.404', [], 404);
})->name('fallback');
