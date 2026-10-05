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

        $query = CustomOrder::with('product')->latest()->orderByDesc('id');

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

    public function export(Request $request): StreamedResponse
    {
        $activeStatus = $request->query('status');

        if (! in_array($activeStatus, array_keys(CustomOrder::STATUS_LABELS), true)) {
            $activeStatus = null;
        }

        $query = CustomOrder::with('product')->latest()->orderByDesc('id');

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

        $filename = 'pesanan-'.($activeStatus ?: 'semua').'-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'ID', 'Token', 'Nama', 'WhatsApp', 'Produk', 'Qty', 'Deadline',
                'Estimasi', 'Jenis', 'Kirim/Ambil', 'Alamat', 'Ukuran', 'Status', 'Detail', 'Dibuat',
            ]);

            $query->chunk(200, function ($orders) use ($handle) {
                foreach ($orders as $order) {
                    fputcsv($handle, [
                        $order->id,
                        $order->tracking_token,
                        $order->name,
                        $order->whatsapp_number,
                        $order->product?->title ?? 'Custom',
                        $order->quantity,
                        $order->deadline?->format('d/m/Y'),
                        $order->is_express ? 'Express' : 'Reguler',
                        $order->service_type,
                        $order->delivery_method,
                        $order->address,
                        $order->size_quantities
                            ? collect($order->size_quantities)->map(fn ($qty, $size) => $size.':'.$qty)->implode(' ')
                            : null,
                        $order->statusLabel(),
                        $order->order_details,
                        $order->created_at?->format('d/m/Y H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function show(CustomOrder $order): View
    {
        $order->load(['product', 'statusHistory.changer']);

        return view('admin.orders.show', compact('order'));
    }

    public function print(CustomOrder $order): View
    {
        $order->load('product');

        return view('admin.orders.print', compact('order'));
    }

    public function updateStatus(Request $request, CustomOrder $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,production,completed,cancelled'],
        ]);

        $previous = $order->status;

        if ($previous !== $validated['status']) {
            $order->forceFill(['status' => $validated['status']])->save();

            $order->statusHistory()->create([
                'from_status' => $previous,
                'to_status' => $validated['status'],
                'changed_by' => $request->user()?->id,
            ]);
        }

        return redirect()
            ->route('admin.orders.show', $order)
            ->with('status', 'Status pesanan diperbarui menjadi "'.$order->statusLabel().'".');
    }

    public function downloadDesign(CustomOrder $order): StreamedResponse
    {
        abort_unless($order->design_file, 404);
        abort_unless(Storage::disk('local')->exists($order->design_file), 404);

        return Storage::disk('local')->download($order->design_file, 'desain-'.$order->id.'-'.basename($order->design_file));
    }
}
