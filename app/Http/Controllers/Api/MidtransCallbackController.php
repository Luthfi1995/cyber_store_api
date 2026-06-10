<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MidtransCallbackController extends Controller
{
    /**
     * Handle payment notification from Midtrans.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        
        Log::info('Midtrans Webhook Received', $payload);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        if (!$orderId || !$statusCode || !$grossAmount) {
            return response()->json(['message' => 'Parameter tidak lengkap.'], 400);
        }

        // Retrieve server key for signature verification
        $serverKey = config('services.midtrans.server_key') ?: env('MIDTRANS_SERVER_KEY', '');

        // Verify Signature Key if Server Key is configured
        if (!empty($serverKey)) {
            // Midtrans sends gross_amount as a string (often with decimals like '10000.00' or without).
            // We should ensure it matches what Midtrans passed exactly.
            $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
            
            if ($signatureKey !== $expectedSignature) {
                Log::warning('Midtrans Webhook Invalid Signature', [
                    'received' => $signatureKey,
                    'expected' => $expectedSignature,
                    'order_id' => $orderId
                ]);
                return response()->json(['message' => 'Tanda tangan tidak valid.'], 403);
            }
        }

        // Find the order by invoice_number
        $order = Order::where('invoice_number', $orderId)->with(['payment', 'items.product'])->first();

        if (!$order) {
            return response()->json(['message' => 'Order tidak ditemukan.'], 404);
        }

        if (!$order->payment) {
            return response()->json(['message' => 'Data pembayaran order tidak ditemukan.'], 404);
        }

        // Avoid re-processing already paid or cancelled orders
        if ($order->payment->status === Payment::STATUS_PAID) {
            return response()->json(['message' => 'Order sudah dibayar sebelumnya.']);
        }

        try {
            DB::transaction(function () use ($order, $transactionStatus) {
                if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                    // Update Payment and Order to PAID
                    $order->payment->update([
                        'status' => Payment::STATUS_PAID,
                        'paid_at' => now(),
                    ]);

                    $order->update([
                        'status' => Order::STATUS_PAID,
                    ]);

                    $order->trackings()->create([
                        'status' => Order::STATUS_PAID,
                        'description' => 'Pembayaran virtual account berhasil diverifikasi oleh Midtrans.',
                        'location' => $order->address?->city ?? 'Sistem',
                    ]);

                    Log::info("Order {$order->invoice_number} successfully paid.");

                } elseif (in_array($transactionStatus, ['cancel', 'deny', 'failure', 'expire'], true)) {
                    
                    $isExpire = ($transactionStatus === 'expire');
                    $newPaymentStatus = $isExpire ? Payment::STATUS_EXPIRED : Payment::STATUS_FAILED;
                    $statusDescription = $isExpire 
                        ? 'Pesanan dibatalkan otomatis karena batas waktu pembayaran habis.' 
                        : 'Pembayaran virtual account gagal atau dibatalkan.';

                    // Update Payment and Order to EXPIRED/FAILED and CANCELLED
                    $order->payment->update([
                        'status' => $newPaymentStatus,
                    ]);

                    $order->update([
                        'status' => Order::STATUS_CANCELLED,
                    ]);

                    $order->trackings()->create([
                        'status' => Order::STATUS_CANCELLED,
                        'description' => $statusDescription,
                        'location' => $order->address?->city ?? 'Sistem',
                    ]);

                    // Restore Product stock
                    foreach ($order->items as $item) {
                        $product = $item->product;
                        if ($product) {
                            $product->increment('stock', $item->quantity);
                            
                            $product->stockMovements()->create([
                                'user_id' => $order->user_id,
                                'type' => 'in',
                                'quantity' => $item->quantity,
                                'reference' => $order->invoice_number,
                                'note' => $isExpire 
                                    ? 'Restock: Waktu pembayaran habis (Midtrans Expire)'
                                    : 'Restock: Pembayaran gagal/batal (Midtrans Cancel/Deny/Failure)',
                            ]);
                        }
                    }

                    Log::info("Order {$order->invoice_number} cancelled. Stock restored.");
                }
            });

            return response()->json(['message' => 'Status pembayaran berhasil diperbarui.']);

        } catch (\Exception $e) {
            Log::error('Error processing Midtrans Callback transaction', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);

            return response()->json(['message' => 'Terjadi kesalahan internal.'], 500);
        }
    }
}
