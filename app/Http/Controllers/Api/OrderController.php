<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['payment', 'expedition'])
            ->where('user_id', $request->user()->id)
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->status))
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json($orders);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        return response()->json([
            'order' => $order->load(['items.product', 'payment', 'trackings', 'address', 'expedition']),
            'status_labels' => Order::statuses(),
        ]);
    }

    public function complete(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if ($order->status !== Order::STATUS_ARRIVED) {
            return response()->json(['message' => 'Pesanan belum dapat diselesaikan.'], 422);
        }

        $order->update(['status' => Order::STATUS_COMPLETED]);
        $order->trackings()->create([
            'status' => Order::STATUS_COMPLETED,
            'description' => 'Pesanan telah diselesaikan oleh customer.',
        ]);

        return response()->json([
            'message' => 'Pesanan selesai.',
            'order' => $order->fresh()->load(['items', 'payment', 'trackings']),
        ]);
    }
}
