<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        return response()->json([
            'cart' => $cart->load(['items.product.category']),
        ]);
    }

    public function add(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'size' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:50'],
        ]);

        $product = Product::where('is_active', true)->findOrFail($validated['product_id']);

        if ($product->stock < $validated['quantity']) {
            return response()->json(['message' => 'Stok produk tidak mencukupi.'], 422);
        }

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);

        // Find existing item with the same product, size, and color
        $item = $cart->items()
            ->where('product_id', $product->id)
            ->where('size', $validated['size'] ?? null)
            ->where('color', $validated['color'] ?? null)
            ->first();

        if ($item) {
            $newQuantity = $item->quantity + $validated['quantity'];

            if ($product->stock < $newQuantity) {
                return response()->json(['message' => 'Stok produk tidak mencukupi untuk jumlah baru.'], 422);
            }

            $item->update(['quantity' => $newQuantity]);
        } else {
            $item = $cart->items()->create($validated);
        }

        return response()->json([
            'message' => 'Produk berhasil ditambahkan ke keranjang.',
            'cart' => $cart->fresh()->load(['items.product.category']),
        ], 201);
    }

    public function update(Request $request, int $itemId): JsonResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $cart = Cart::firstOrCreate(['user_id' => $request->user()->id]);
        $item = $cart->items()->where('id', $itemId)->firstOrFail();

        if ($item->product->stock < $validated['quantity']) {
            return response()->json(['message' => 'Stok produk tidak mencukupi.'], 422);
        }

        $item->update($validated);

        return response()->json([
            'message' => 'Keranjang berhasil diperbarui.',
            'cart' => $cart->fresh()->load(['items.product.category']),
        ]);
    }

    public function remove(Request $request, int $itemId): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();
        $cart->items()->where('id', $itemId)->firstOrFail()->delete();

        return response()->json([
            'message' => 'Item berhasil dihapus dari keranjang.',
            'cart' => $cart->fresh()->load(['items.product.category']),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = Cart::where('user_id', $request->user()->id)->firstOrFail();
        $cart->items()->delete();

        return response()->json(['message' => 'Keranjang berhasil dikosongkan.']);
    }
}
