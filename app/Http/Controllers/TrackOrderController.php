<?php

namespace App\Http\Controllers;

use App\Models\CustomOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrackOrderController extends Controller
{
    public function create(): View
    {
        return view('orders.track', ['order' => null]);
    }

    public function store(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'integer', 'min:1'],
            'whatsapp_number' => ['required', 'string', 'max:32', 'regex:/^[0-9+\-\s()]+$/'],
        ], [
            'whatsapp_number.regex' => 'Nomor WhatsApp hanya boleh berisi angka dan tanda + - ( ).',
        ]);

        $order = CustomOrder::find($validated['order_id']);

        if (! $order || ! $order->matchesPhone($validated['whatsapp_number'])) {
            return back()->withErrors([
                'order_id' => 'Nomor order atau nomor WhatsApp tidak cocok.',
            ])->onlyInput('order_id');
        }

        return view('orders.track', compact('order'));
    }
}
