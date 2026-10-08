<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CustomOrder;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $chart = $this->dailyOrdersChart();

        return view('admin.dashboard', [
            'admin' => $request->user(),
            'stats' => [
                'pending' => CustomOrder::where('status', CustomOrder::STATUS_PENDING)->count(),
                'production' => CustomOrder::where('status', CustomOrder::STATUS_PRODUCTION)->count(),
                'completed' => CustomOrder::where('status', CustomOrder::STATUS_COMPLETED)->count(),
                'cancelled' => CustomOrder::where('status', CustomOrder::STATUS_CANCELLED)->count(),
                'orders' => CustomOrder::count(),
                'products' => Product::count(),
                'categories' => Category::count(),
                'month' => CustomOrder::where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'chart' => $chart,
            'chartMax' => max(1, max(array_column($chart, 'count'))),
            'recentOrders' => CustomOrder::with('product')->latest()->orderByDesc('id')->limit(5)->get(),
        ]);
    }

    private function dailyOrdersChart(): array
    {
        $counts = CustomOrder::where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('date(created_at) as day, count(*) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        return collect(range(29, 0))->map(function (int $offset) use ($counts) {
            $date = now()->subDays($offset);

            return [
                'label' => $date->format('d/m'),
                'count' => (int) ($counts[$date->format('Y-m-d')] ?? 0),
            ];
        })->values()->all();
    }
}
