<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use App\Models\Product;
use App\Notifications\NewOrderNotification;
use App\Support\ServiceTypes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
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

        return view('orders.create', [
            'products' => $products,
            'preselected' => $preselected,
            'serviceTypes' => ServiceTypes::titles(),
            'sizes' => config('banjarcustom.sizes'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        // Honeypot: kolom ini harus kosong. Bot yang mengisinya dibuang diam-diam.
        if ($request->filled('website')) {
            Log::info('Order honeypot triggered', ['ip' => $request->ip()]);

            return redirect()->route('home');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'whatsapp_number' => ['required', 'string', 'max:32', 'regex:/^[0-9+\-\s()]+$/'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'deadline' => ['nullable', 'date', 'after_or_equal:today'],
            'service_type' => ['nullable', 'string', 'max:60'],
            'delivery_method' => ['nullable', 'in:kirim,ambil'],
            'express' => ['sometimes', 'boolean'],
            'address' => ['required_if:delivery_method,kirim', 'nullable', 'string', 'max:500'],
            'sizes' => ['nullable', 'array'],
            'sizes.*' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'order_details' => ['nullable', 'string', 'min:10', 'max:3000'],
            'design_file' => ['nullable', 'file', 'max:5120', 'extensions:jpg,jpeg,png,webp,pdf,ai,psd,zip'],
        ], [
            'whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka dan tanda + - ( ).',
            'deadline.after_or_equal' => 'Deadline tidak boleh sebelum hari ini.',
            'address.required_if' => 'Alamat pengiriman wajib diisi jika pesanan dikirim.',
            'order_details.min' => 'Detail pesanan minimal 10 karakter.',
            'design_file.extensions' => 'Format file harus jpg, png, webp, pdf, ai, psd, atau zip.',
        ]);

        $sizes = collect($validated['sizes'] ?? [])->filter(fn ($qty) => $qty > 0);

        if ($sizes->isNotEmpty() && empty($validated['quantity'])) {
            $validated['quantity'] = (int) $sizes->sum();
        }

        $validated['size_quantities'] = $sizes->all() ?: null;
        unset($validated['sizes']);

        if (! $validated['order_details']) {
            $validated['order_details'] = null;
        }

        if ($request->hasFile('design_file')) {
            $validated['design_file'] = $request->file('design_file')->store('designs', 'local');
        }

        $validated['is_express'] = $request->boolean('express');

        $order = CustomOrder::create($validated);

        $this->notifyAdmins($order);

        return redirect()->to($order->successUrl());
    }

    private function notifyAdmins(CustomOrder $order): void
    {
        try {
            $recipients = NewOrderNotification::recipients();

            if ($recipients !== []) {
                Notification::route('mail', $recipients)->notify(new NewOrderNotification($order));
            }
        } catch (\Throwable $e) {
            // Pesanan tetap tersimpan walau email gagal terkirim.
            Log::warning('Notifikasi pesanan baru gagal dikirim', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function success(string $token): View
    {
        $order = CustomOrder::where('tracking_token', $token)->firstOrFail();

        return view('orders.success', compact('order'));
    }
}
