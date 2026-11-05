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
        if (! in_array($status, CustomOrder::PRODUCTION_STAGES, true)) {
            $status = '';
        }

        $mine = $request->boolean('milik');

        $query = CustomOrder::query()
            ->whereIn('status', CustomOrder::PRODUCTION_STAGES)
            ->orderByRaw('deadline IS NULL')
            ->orderBy('deadline')
            ->orderByDesc('id');

        if ($status !== '') {
            $query->where('status', $status);
        }

        if ($mine) {
            $query->where('assigned_operator', (string) $request->user()?->name);
        }

        $counts = [];

        foreach (CustomOrder::PRODUCTION_STAGES as $stage) {
            $counts[$stage] = CustomOrder::where('status', $stage)->count();
        }

        return view('admin.produksi.index', [
            'orders' => $query->get(),
            'activeStatus' => $status,
            'mine' => $mine,
            'counts' => $counts,
        ]);
    }
}
