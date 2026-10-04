<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $activeStatus = $request->query('status');

        if (! in_array($activeStatus, array_keys(CustomOrder::STATUS_LABELS), true)) {
            $activeStatus = null;
        }

        $query = CustomOrder::with('product')->latest();

        if ($activeStatus) {
            $query->where('status', $activeStatus);
        }

        if ($request->filled('q')) {
            $search = $request->string('q')->trim();

            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('whatsapp_number', 'like', "%{$search}%");
            });
        }

        return view('admin.orders.index', [
            'orders' => $query->paginate(10)->withQueryString(),
            'activeStatus' => $activeStatus,
            'search' => $request->query('q', ''),
            'statusLabels' => CustomOrder::STATUS_LABELS,
            'statusCounts' => [
                'all' => CustomOrder::count(),
                ...CustomOrder::query()
                    ->selectRaw('status, count(*) as total')
                    ->groupBy('status')
                    ->pluck('total', 'status')
                    ->all(),
            ],
        ]);
    }

    public function show(CustomOrder $order): View
    {
        $order->load('product');

        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, CustomOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,production,completed'],
        ]);

        $order->update($validated);

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Status pesanan diperbarui menjadi "'.$order->statusLabel().'".');
    }

    public function downloadDesign(CustomOrder $order): StreamedResponse
    {
        abort_unless($order->design_file, 404);
        abort_unless(Storage::disk('public')->exists($order->design_file), 404);

        return Storage::disk('public')->download($order->design_file, 'desain-'.$order->id.'-'.basename($order->design_file));
    }
}
