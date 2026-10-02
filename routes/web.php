<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/produk', [CatalogController::class, 'index'])->name('produk.index');
Route::get('/kategori/{slug}', [CatalogController::class, 'category'])->name('kategori');
Route::get('/produk/{slug}', [CatalogController::class, 'show'])->name('produk.show');

Route::get('/galeri', [GalleryController::class, 'index'])->name('galeri');

Route::get('/pesan', [OrderController::class, 'create'])->name('pesan.create');
Route::post('/pesan', [OrderController::class, 'store'])->name('pesan.store');
Route::get('/pesan/sukses/{order}', [OrderController::class, 'success'])->name('pesan.success');

Route::get('/tentang', [PageController::class, 'tentang'])->name('tentang');
Route::get('/panduan-ukuran', [PageController::class, 'sizeChart'])->name('size-chart');
Route::get('/kebijakan-privasi', [PageController::class, 'kebijakanPrivasi'])->name('kebijakan-privasi');
Route::get('/syarat-ketentuan', [PageController::class, 'syaratKetentuan'])->name('syarat-ketentuan');
Route::get('/login', [PageController::class, 'login'])->name('login');
