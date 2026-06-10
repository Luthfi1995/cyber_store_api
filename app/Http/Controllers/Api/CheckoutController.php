<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\MidtransService;
use App\Models\Payment;

class CheckoutController extends Controller
{
    public function store(Request $request, MidtransService $midtransService): JsonResponse
    {
        $validated = $request->validate([
            'customer_address_id' => ['required', 'exists:customer_addresses,id'],
            'expedition_id' => ['required', 'exists:expeditions,id'],
            'bank_code' => ['required', 'in:bca,bni,bri,mandiri,permata'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        $address = CustomerAddress::where('id', $validated['customer_address_id'])
            ->where('user_id', $user->id)
            ->firstOrFail();

        if (blank($address->latitude) || blank($address->longitude)) {
            return response()->json([
                'message' => 'Lokasi belum lengkap. Silakan pilih titik lokasi pada map terlebih dahulu.',
            ], 422);
        }

        $expedition = Expedition::where('is_active', true)->findOrFail($validated['expedition_id']);

        $cart = Cart::where('user_id', $user->id)
            ->with(['items.product'])
            ->firstOrFail();

        if ($cart->items->isEmpty()) {
            return response()->json(['message' => 'Keranjang masih kosong.'], 422);
        }

        $order = DB::transaction(function () use ($cart, $user, $address, $expedition, $validated, $midtransService) {
            $subtotal = 0;

            foreach ($cart->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->firstOrFail();

                if (! $product->is_active || $product->stock < $item->quantity) {
                    abort(422, "Stok {$product->name} tidak mencukupi.");
                }

                $subtotal += $product->price * $item->quantity;
            }

            $totalQuantity = $cart->items->sum('quantity');
            $shippingCost = $expedition->base_cost + max(0, $totalQuantity - 1) * 1000;
            $grandTotal = $subtotal + $shippingCost;

            $order = Order::create([
                'invoice_number' => 'INV-'.now()->format('YmdHis').'-'.strtoupper(Str::random(5)),
                'user_id' => $user->id,
                'customer_address_id' => $address->id,
                'expedition_id' => $expedition->id,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'grand_total' => $grandTotal,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'note' => $validated['note'] ?? null,
            ]);

            foreach ($cart->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->firstOrFail();

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'size' => $item->size,
                    'color' => $item->color,
                    'price' => $product->price,
                    'quantity' => $item->quantity,
                    'total' => $product->price * $item->quantity,
                ]);

                $product->decrement('stock', $item->quantity);

                $product->stockMovements()->create([
                    'user_id' => $user->id,
                    'type' => 'out',
                    'quantity' => $item->quantity,
                    'reference' => $order->invoice_number,
                    'note' => 'Checkout customer',
                ]);
            }

            $paymentData = $midtransService->createVirtualAccount($order->invoice_number, $grandTotal, $validated['bank_code']);

            $order->payment()->create([
                'bank_code' => $validated['bank_code'],
                'virtual_account_number' => $paymentData['va_number'] ?? $paymentData['bill_key'] ?? 'N/A',
                'biller_code' => $paymentData['biller_code'] ?? null,
                'amount' => $grandTotal,
                'status' => Payment::STATUS_WAITING_PAYMENT,
                'expired_at' => now()->addDay(),
                'external_reference' => $paymentData['transaction_id'] ?? 'VA-'.strtoupper(Str::random(12)),
            ]);

            $order->trackings()->create([
                'status' => Order::STATUS_PENDING_PAYMENT,
                'description' => 'Pesanan dibuat dan menunggu pembayaran virtual account.',
                'location' => $address->city,
            ]);

            $cart->items()->delete();

            return $order;
        });

        return response()->json([
            'message' => 'Checkout berhasil.',
            'order' => $order->load(['items', 'payment', 'trackings', 'address', 'expedition']),
        ], 201);
    }
}
