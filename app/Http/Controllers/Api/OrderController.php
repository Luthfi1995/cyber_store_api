<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $orders = Order::with(['payment', 'expedition', 'items.product'])
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
            'store_name' => \App\Models\Setting::get('store_name', 'UBSI Store'),
            'store_address' => \App\Models\Setting::get('store_address', 'Jl. Kramat Raya No.98, Senen, Jakarta Pusat'),
            'store_email' => \App\Models\Setting::get('store_email', 'support@bsi.ac.id'),
            'store_phone' => \App\Models\Setting::get('store_phone', '(021) 7867868'),
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

    public function trackWaybill(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        $result = $order->syncTracking();

        if (!$result['success']) {
            return response()->json(['message' => $result['message']], 422);
        }

        return response()->json([
            'message' => $result['message'],
            'order' => $order->fresh()->load(['items.product', 'payment', 'trackings', 'address', 'expedition']),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        abort_if($order->user_id !== $request->user()->id, 403);

        if ($order->status !== Order::STATUS_PENDING_PAYMENT) {
            return response()->json(['message' => 'Hanya pesanan yang belum dibayar yang dapat dibatalkan.'], 422);
        }

        $reason = $request->input('reason', 'Tidak ada alasan khusus');

        try {
            DB::transaction(function () use ($order, $reason) {
                if ($order->payment) {
                    $order->payment->update([
                        'status' => \App\Models\Payment::STATUS_FAILED,
                    ]);
                }

                $order->update([
                    'status' => Order::STATUS_CANCELLED,
                    'note' => $order->note ? $order->note . ' | Alasan Batal: ' . $reason : 'Alasan Batal: ' . $reason,
                ]);

                $order->trackings()->create([
                    'status' => Order::STATUS_CANCELLED,
                    'description' => 'Pesanan dibatalkan oleh pembeli. Alasan: ' . $reason,
                    'location' => $order->address?->city ?? 'Sistem',
                ]);

                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                        
                        $product->stockMovements()->create([
                            'user_id' => $order->user_id,
                            'type' => 'in',
                            'quantity' => $item->quantity,
                            'reference' => $order->invoice_number,
                            'note' => 'Restock: Dibatalkan oleh pembeli (Cancel order)',
                        ]);
                    }
                }
            });

            return response()->json([
                'message' => 'Pesanan berhasil dibatalkan.',
                'order' => $order->fresh()->load(['items.product', 'payment', 'trackings', 'address', 'expedition']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal membatalkan pesanan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
