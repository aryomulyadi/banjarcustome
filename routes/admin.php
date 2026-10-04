<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::patch('/pesanan/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
    Route::get('/pesanan/{order}/design', [OrderController::class, 'downloadDesign'])->name('orders.design');

    Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
    Route::get('/produk/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/produk', [ProductController::class, 'store'])->name('products.store');
    Route::get('/produk/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/produk/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/produk/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

    Route::get('/kategori', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/kategori/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/kategori', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/kategori/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/kategori/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/kategori/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/galeri', [GalleryController::class, 'index'])->name('galleries.index');
    Route::get('/galeri/create', [GalleryController::class, 'create'])->name('galleries.create');
    Route::post('/galeri', [GalleryController::class, 'store'])->name('galleries.store');
    Route::get('/galeri/{gallery}/edit', [GalleryController::class, 'edit'])->name('galleries.edit');
    Route::put('/galeri/{gallery}', [GalleryController::class, 'update'])->name('galleries.update');
    Route::delete('/galeri/{gallery}', [GalleryController::class, 'destroy'])->name('galleries.destroy');
});
