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

        $reason = $request->input('reason', 'Tidak ada alasan khusus');

        // Case 1: Pending payment -> Cancel immediately
        if ($order->status === Order::STATUS_PENDING_PAYMENT) {
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

        // Case 2: Paid or Packed -> Request cancellation
        if (in_array($order->status, [Order::STATUS_PAID, Order::STATUS_PACKED])) {
            if ($order->cancel_request_status === 'pending') {
                return response()->json(['message' => 'Pengajuan pembatalan untuk pesanan ini sedang diproses oleh Admin.'], 422);
            }

            if ($order->cancel_request_status === 'approved') {
                return response()->json(['message' => 'Pengajuan pembatalan sudah disetujui.'], 422);
            }

            $order->update([
                'cancel_request_status' => 'pending',
                'cancel_request_reason' => $reason,
            ]);

            $order->trackings()->create([
                'status' => $order->status,
                'description' => 'Mengajukan pembatalan pesanan. Alasan: ' . $reason,
                'location' => 'Customer App',
            ]);

            return response()->json([
                'message' => 'Pengajuan pembatalan pesanan berhasil dikirim ke Admin.',
                'order' => $order->fresh()->load(['items.product', 'payment', 'trackings', 'address', 'expedition']),
            ]);
        }

        // Case 3: Other statuses -> Cannot cancel
        return response()->json(['message' => 'Pesanan tidak dapat dibatalkan atau diajukan pembatalan karena sudah dikirim/selesai.'], 422);
    }
}
