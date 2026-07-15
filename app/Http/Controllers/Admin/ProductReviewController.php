<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Models\ProductReviewReply;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductReviewController extends Controller
{
    /**
     * Display a listing of product review chats.
     */
    public function index(Request $request)
    {
        $query = ProductReview::with(['user', 'product', 'replies'])
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                  ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('product', fn ($p) => $p->where('name', 'like', "%{$search}%"));
            });
        }

        $reviews = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'reviews' => $reviews->items(),
            ]);
        }

        return view('admin.review-chats.index', compact('reviews'));
    }

    /**
     * Display the specified review chat.
     */
    public function show(ProductReview $review)
    {
        $review->load(['user', 'product', 'replies.user']);

        $reviews = ProductReview::with(['user', 'product', 'replies'])
            ->whereNotNull('comment')
            ->where('comment', '!=', '')
            ->latest()
            ->take(50)
            ->get();

        if (request()->wantsJson()) {
            return response()->json([
                'review' => $review,
                'replies' => $review->replies->map(function ($r) {
                    return [
                        'id' => $r->id,
                        'reply' => $r->reply,
                        'admin_name' => $r->user?->name ?? 'Admin',
                        'admin_photo' => $r->user?->photo,
                        'created_at' => $r->created_at->toIso8601String()
                    ];
                })
            ]);
        }

        return view('admin.review-chats.index', compact('review', 'reviews'));
    }

    /**
     * Admin submits a reply to the review.
     */
    public function reply(Request $request, ProductReview $review)
    {
        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $reply = ProductReviewReply::create([
            'product_review_id' => $review->id,
            'user_id' => auth()->id(),
            'reply' => $request->input('message'),
        ]);

        // Fallback update on product_reviews reply column for backward compatibility
        $review->update([
            'reply' => $request->input('message')
        ]);

        // Clear products and reviews caches
        Cache::store('redis')->forget("reviews:product:{$review->product_id}");
        Cache::store('redis')->forget("product:detail:{$review->product_id}");

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'reply' => [
                    'id' => $reply->id,
                    'reply' => $reply->reply,
                    'admin_name' => auth()->user()->name,
                    'admin_photo' => auth()->user()->photo,
                    'created_at' => $reply->created_at->toIso8601String()
                ]
            ]);
        }

        return back()->with('success', 'Respon ulasan berhasil dikirim.');
    }
}
