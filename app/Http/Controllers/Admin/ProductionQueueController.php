<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomOrder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductionQueueController extends Controller
{
    public function index(Request $request): View
    {
        $status = (string) $request->query('status');
        if (! in_array($status, [CustomOrder::STATUS_READY, CustomOrder::STATUS_PRODUCTION], true)) {
            $status = '';
        }

        $mine = $request->boolean('milik');

        $query = CustomOrder::query()
            ->whereIn('status', [CustomOrder::STATUS_READY, CustomOrder::STATUS_PRODUCTION])
            ->orderByRaw('deadline IS NULL')
            ->orderBy('deadline')
            ->orderByDesc('id');

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($mine) {
            $query->where('assigned_operator', (string) $request->user()?->name);
        }

        return view('admin.produksi.index', [
            'orders' => $query->get(),
            'activeStatus' => $status,
            'mine' => $mine,
            'counts' => [
                CustomOrder::STATUS_READY => CustomOrder::where('status', CustomOrder::STATUS_READY)->count(),
                CustomOrder::STATUS_PRODUCTION => CustomOrder::where('status', CustomOrder::STATUS_PRODUCTION)->count(),
            ],
        ]);
    }
}
