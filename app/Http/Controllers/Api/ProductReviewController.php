<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductReviewController extends Controller
{
    /**
     * Get reviews for a specific product.
     */
    public function index(Product $product): JsonResponse
    {
        $reviews = Cache::store('redis')->remember(
            "reviews:product:{$product->id}",
            now()->addMinutes(30),
            function () use ($product) {
                return ProductReview::with('user:id,name,photo')
                    ->where('product_id', $product->id)
                    ->latest()
                    ->get()
                    ->toArray();
            }
        );

        return response()->json($reviews);
    }

    /**
     * Submit a review for a product.
     */
    public function store(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => ['required', 'exists:orders,id'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $user = $request->user();

        // Check if order exists, belongs to user, and is completed
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', $user->id)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Pesanan tidak ditemukan.'], 404);
        }

        if ($order->status !== Order::STATUS_COMPLETED) {
            return response()->json(['message' => 'Pesanan belum diselesaikan.'], 400);
        }

        // Verify the product is part of this order
        $orderItem = $order->items()->where('product_id', $product->id)->first();
        if (!$orderItem) {
            return response()->json(['message' => 'Produk tidak ditemukan dalam pesanan ini.'], 404);
        }

        // Check if review already exists
        $exists = ProductReview::where('order_id', $order->id)
            ->where('product_id', $product->id)
            ->exists();

        if ($exists) {
            return response()->json(['message' => 'Anda sudah memberikan ulasan untuk produk ini.'], 400);
        }

        // Create the review
        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
        ]);

        // Recalculate average rating & reviews count
        $avgRating = ProductReview::where('product_id', $product->id)->avg('rating') ?? 4.8;
        $reviewsCount = ProductReview::where('product_id', $product->id)->count();

        $product->update([
            'rating' => round($avgRating, 1),
            'reviews_count' => $reviewsCount,
        ]);

        // Hapus cache review dan detail produk agar data terbaru langsung tampil
        Cache::store('redis')->forget("reviews:product:{$product->id}");
        Cache::store('redis')->forget("product:detail:{$product->id}");

        return response()->json([
            'message' => 'Ulasan berhasil dikirim.',
            'review' => $review->load('user:id,name,photo'),
        ], 201);
    }

    /**
     * Get all reviews written by the authenticated user.
     */
    public function myReviews(Request $request): JsonResponse
    {
        $reviews = ProductReview::with(['product:id,name,images', 'product.images'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate($request->integer('per_page', 10));

        return response()->json($reviews);
    }
}
