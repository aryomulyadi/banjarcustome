<?php

use App\Http\Controllers\Admin\CalculatorController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Admin\JerseyDataController;
use App\Http\Controllers\Admin\MasterDataController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductionQueueController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\TargetDataController;
use App\Http\Controllers\Admin\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    // Khusus admin. Didahulukan agar path literal (/pesanan/export, /pesanan/import, …)
    // tidak tertangkap parameter {order} di grup bawah.
    Route::middleware('role:admin')->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/pesanan', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/pesanan/export', [OrderController::class, 'export'])->name('orders.export');
        Route::get('/pesanan/import', [OrderController::class, 'importForm'])->name('orders.import');
        Route::get('/pesanan/import/template', [OrderController::class, 'importTemplate'])->name('orders.import.template');
        Route::post('/pesanan/import', [OrderController::class, 'importStore'])->name('orders.import.store');
        Route::get('/pesanan/{order}/cetak', [OrderController::class, 'print'])->name('orders.print');
        Route::patch('/pesanan/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::patch('/pesanan/{order}/harga', [OrderController::class, 'updatePrice'])->name('orders.price');
        Route::patch('/pesanan/{order}/spesifikasi', [OrderController::class, 'updateSpec'])->name('orders.spec');
        Route::get('/pesanan/{order}/design', [OrderController::class, 'downloadDesign'])->name('orders.design');
        Route::post('/pesanan/{order}/desain', [OrderController::class, 'updateDesign'])->name('orders.design.upload');
        Route::post('/pesanan/{order}/desain/disetujui', [OrderController::class, 'approveDesign'])->name('orders.design.approve');
        Route::get('/pesanan/{order}/desain/preview', [OrderController::class, 'designPreview'])->name('orders.design.preview');
        Route::patch('/pesanan/{order}/dp', [OrderController::class, 'updateDp'])->name('orders.dp');
        Route::patch('/pesanan/{order}/serah-terima', [OrderController::class, 'updateHandover'])->name('orders.handover');

        Route::get('/kalkulator', [CalculatorController::class, 'index'])->name('calculator');
        Route::get('/kalkulator/cetak', [CalculatorController::class, 'print'])->name('calculator.print');

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

        Route::get('/faq', [FaqController::class, 'index'])->name('faqs.index');
        Route::get('/faq/create', [FaqController::class, 'create'])->name('faqs.create');
        Route::post('/faq', [FaqController::class, 'store'])->name('faqs.store');
        Route::get('/faq/{faq}/edit', [FaqController::class, 'edit'])->name('faqs.edit');
        Route::put('/faq/{faq}', [FaqController::class, 'update'])->name('faqs.update');
        Route::delete('/faq/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');

        Route::get('/testimoni', [TestimonialController::class, 'index'])->name('testimonials.index');
        Route::get('/testimoni/create', [TestimonialController::class, 'create'])->name('testimonials.create');
        Route::post('/testimoni', [TestimonialController::class, 'store'])->name('testimonials.store');
        Route::get('/testimoni/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('testimonials.edit');
        Route::put('/testimoni/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
        Route::delete('/testimoni/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');

        Route::get('/master-data', [MasterDataController::class, 'index'])->name('master.index');
        Route::post('/master-data', [MasterDataController::class, 'store'])->name('master.store');
        Route::get('/master-data/{option}/edit', [MasterDataController::class, 'edit'])->name('master.edit');
        Route::patch('/master-data/{option}', [MasterDataController::class, 'update'])->name('master.update');
        Route::delete('/master-data/{option}', [MasterDataController::class, 'destroy'])->name('master.destroy');

        // Dua modul operasional terpisah (struktur kolom berbeda, jangan digabung).
        Route::get('/data-jersey', [JerseyDataController::class, 'index'])->name('jersey.index');
        Route::get('/data-jersey/create', [JerseyDataController::class, 'create'])->name('jersey.create');
        Route::post('/data-jersey', [JerseyDataController::class, 'store'])->name('jersey.store');
        Route::get('/data-jersey/import', [JerseyDataController::class, 'importForm'])->name('jersey.import');
        Route::post('/data-jersey/import', [JerseyDataController::class, 'importStore'])->name('jersey.import.store');
        Route::patch('/data-jersey/bulk-status', [JerseyDataController::class, 'bulkStatus'])->name('jersey.bulk-status');
        Route::patch('/data-jersey/{jersey}/status', [JerseyDataController::class, 'updateStatus'])->name('jersey.status');
        Route::get('/data-jersey/{jersey}/edit', [JerseyDataController::class, 'edit'])->name('jersey.edit');
        Route::put('/data-jersey/{jersey}', [JerseyDataController::class, 'update'])->name('jersey.update');
        Route::delete('/data-jersey/{jersey}', [JerseyDataController::class, 'destroy'])->name('jersey.destroy');

        Route::get('/data-target', [TargetDataController::class, 'index'])->name('target.index');
        Route::get('/data-target/create', [TargetDataController::class, 'create'])->name('target.create');
        Route::post('/data-target', [TargetDataController::class, 'store'])->name('target.store');
        Route::get('/data-target/import', [TargetDataController::class, 'importForm'])->name('target.import');
        Route::post('/data-target/import', [TargetDataController::class, 'importStore'])->name('target.import.store');
        Route::get('/data-target/{target}/edit', [TargetDataController::class, 'edit'])->name('target.edit');
        Route::put('/data-target/{target}', [TargetDataController::class, 'update'])->name('target.update');
        Route::delete('/data-target/{target}', [TargetDataController::class, 'destroy'])->name('target.destroy');
    });

    // Admin + Produksi: antrian produksi & ruang kerja lapangan.
    Route::middleware('role:admin,produksi')->group(function () {
        Route::get('/produksi', [ProductionQueueController::class, 'index'])->name('produksi');

        Route::get('/pesanan/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('/pesanan/{order}/progres', [OrderController::class, 'updateProgress'])->name('orders.progress');
        Route::get('/pesanan/{order}/kartu-kerja', [OrderController::class, 'cardPrint'])->name('orders.card');
        Route::get('/pesanan/{order}/spk', [OrderController::class, 'spkPrint'])->name('orders.spk');

        Route::get('/profil', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
    });
});
