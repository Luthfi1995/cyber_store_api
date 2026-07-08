<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        // Cache stats selama 5 menit — data statistik tidak perlu 100% real-time
        $stats = Cache::store('redis')->remember('admin:dashboard:stats', now()->addMinutes(5), function () {
            return [
                'total_users'       => User::count(),
                'total_customers'   => User::where('role', 'customer')->count(),
                'total_admins'      => User::whereIn('role', ['admin', 'superadmin'])->count(),
                'total_products'    => Product::count(),
                'active_products'   => Product::where('is_active', true)->count(),
                'total_categories'  => Category::count(),
                'total_orders'      => Order::count(),
                'total_revenue'     => Order::where('status', Order::STATUS_COMPLETED)->sum('grand_total'),
                'pending_orders'    => Order::where('status', Order::STATUS_PENDING_PAYMENT)->count(),
                'processing_orders' => Order::whereIn('status', [Order::STATUS_PAID, Order::STATUS_PACKED, Order::STATUS_SHIPPED])->count(),
            ];
        });

        $recent_users         = User::latest()->take(5)->get();
        $recent_orders        = Order::with(['user'])->latest()->take(5)->get();
        $low_stock_products   = Product::where('stock', '<=', 10)->where('is_active', true)->orderBy('stock')->take(5)->get();

        return view('admin.dashboard.index', compact('stats', 'recent_users', 'recent_orders', 'low_stock_products'));
    }
}
