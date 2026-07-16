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
    /** Batas stok rendah yang ditampilkan di dashboard. */
    private const LOW_STOCK_THRESHOLD = 10;

    /** Durasi cache statistik dashboard (menit). */
    private const STATS_CACHE_TTL = 5;

    public function index()
    {
        // Cache stats selama 5 menit — data statistik tidak perlu 100% real-time.
        // Menggunakan default cache driver (tidak hardcode 'redis') agar tetap berjalan
        // di environment yang hanya punya driver file/array (mis. CI, local tanpa Redis).
        $stats = Cache::remember(
            'admin:dashboard:stats',
            now()->addMinutes(self::STATS_CACHE_TTL),
            fn () => $this->computeDashboardStats()
        );

        /** @var \Illuminate\Database\Eloquent\Builder $user */
        $user = User::query();
        $recent_users = $user->latest()->take(5)->get();

        /** @var \Illuminate\Database\Eloquent\Builder $order */
        $order = Order::query();
        $recent_orders = $order->with('user')->latest()->take(5)->get();

        /** @var \Illuminate\Database\Eloquent\Builder $product */
        $product = Product::query();
        $low_stock_products = $product->where('stock', '<=', self::LOW_STOCK_THRESHOLD)
            ->where('is_active', true)
            ->orderBy('stock')
            ->take(5)
            ->get();

        return view(
            'admin.dashboard.index',
            compact('stats', 'recent_users', 'recent_orders', 'low_stock_products')
        );
    }

    /**
     * Hitung semua statistik dashboard.
     *
     * Dipisah ke method sendiri agar IDE (Intelephense) dapat menganalisis
     * tipe data Eloquent secara akurat tanpa false positive.
     *
     * @return array<string, int|float|string>
     */
    private function computeDashboardStats(): array
    {
        /** @var \Illuminate\Database\Eloquent\Builder $user */
        $user = User::query();
        /** @var \Illuminate\Database\Eloquent\Builder $product */
        $product = Product::query();
        /** @var \Illuminate\Database\Eloquent\Builder $category */
        $category = Category::query();
        /** @var \Illuminate\Database\Eloquent\Builder $order */
        $order = Order::query();

        return [
            'total_users'       => (clone $user)->count(),
            'total_customers'   => (clone $user)->where('role', User::ROLE_CUSTOMER)->count(),
            'total_admins'      => (clone $user)->whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPERADMIN])->count(),
            'total_products'    => (clone $product)->count(),
            'active_products'   => (clone $product)->where('is_active', true)->count(),
            'total_categories'  => (clone $category)->count(),
            'total_orders'      => (clone $order)->count(),
            'total_revenue'     => (clone $order)->where('status', Order::STATUS_COMPLETED)->sum('grand_total'),
            'pending_orders'    => (clone $order)->where('status', Order::STATUS_PENDING_PAYMENT)->count(),
            'processing_orders' => (clone $order)->whereIn('status', [
                Order::STATUS_PAID,
                Order::STATUS_PACKED,
                Order::STATUS_SHIPPED,
            ])->count(),
        ];
    }
}
