<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function create(Request $request): View
    {
        $products = Product::orderBy('title')->get(['id', 'title']);

        $preselected = null;

        if ($request->filled('produk')) {
            $preselected = Product::where('slug', $request->string('produk'))->value('id');
        }

        return view('orders.create', compact('products', 'preselected'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:32', 'regex:/^[0-9+\-\s()]+$/'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'order_details' => ['required', 'string', 'min:10', 'max:3000'],
            'design_file' => ['nullable', 'file', 'max:5120', 'extensions:jpg,jpeg,png,webp,pdf,ai,psd,zip'],
        ], [
            'whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka dan tanda + - ( ).',
            'order_details.min' => 'Detail pesanan minimal 10 karakter.',
            'design_file.extensions' => 'Format file harus jpg, png, webp, pdf, ai, psd, atau zip.',
        ]);

        if ($request->hasFile('design_file')) {
            $validated['design_file'] = $request->file('design_file')->store('designs', 'public');
        }

        $order = CustomOrder::create($validated);

        return redirect()->route('pesan.success', $order);
    }

    public function success(CustomOrder $order): View
    {
        return view('orders.success', compact('order'));
    }
}
