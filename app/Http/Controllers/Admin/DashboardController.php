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
        return view('admin.dashboard', [
            'admin' => $request->user(),
            'stats' => [
                'pending' => CustomOrder::where('status', CustomOrder::STATUS_PENDING)->count(),
                'production' => CustomOrder::where('status', CustomOrder::STATUS_PRODUCTION)->count(),
                'completed' => CustomOrder::where('status', CustomOrder::STATUS_COMPLETED)->count(),
                'orders' => CustomOrder::count(),
                'products' => Product::count(),
                'categories' => Category::count(),
                'month' => CustomOrder::where('created_at', '>=', now()->startOfMonth())->count(),
            ],
            'recentOrders' => CustomOrder::with('product')->latest()->limit(5)->get(),
        ]);
    }
}
