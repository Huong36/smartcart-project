<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'orderCount' => Order::count(),
            'revenue' => Order::where('status', Order::STATUS_COMPLETED)->sum('total_amount'),
            'productCount' => Product::count(),
            'stockCount' => Product::sum('stock'),
            'categoryCount' => Category::count(),
            'recentOrders' => Order::with('user')->latest('id')->limit(5)->get(),
            'statusCounts' => Order::selectRaw('status, COUNT(*) AS total')->groupBy('status')->pluck('total', 'status'),
        ]);
    }
}
