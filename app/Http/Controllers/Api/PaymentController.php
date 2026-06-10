<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function simulatePaid(Request $request, Payment $payment): JsonResponse
    {
        $payment->load('order');
        abort_if($payment->order->user_id !== $request->user()->id, 403);

        if ($payment->status === Payment::STATUS_PAID) {
            return response()->json(['message' => 'Pembayaran sudah berhasil.']);
        }

        DB::transaction(function () use ($payment) {
            $payment->update([
                'status' => Payment::STATUS_PAID,
                'paid_at' => now(),
            ]);

            $payment->order->update([
                'status' => Order::STATUS_PAID,
            ]);

            $payment->order->trackings()->create([
                'status' => Order::STATUS_PAID,
                'description' => 'Pembayaran virtual account berhasil diverifikasi.',
            ]);
        });

        return response()->json([
            'message' => 'Pembayaran berhasil disimulasikan.',
            'payment' => $payment->fresh(),
            'order' => $payment->order->fresh()->load(['payment', 'trackings']),
        ]);
    }
}
