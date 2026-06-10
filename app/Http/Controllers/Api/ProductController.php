<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->with(['category', 'images'])
            ->where('is_active', true)
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->category_id))
            ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->has('is_recommended'), fn ($query) => $query->where('is_recommended', $request->boolean('is_recommended')))
            ->latest()
            ->paginate($request->integer('per_page', 12));

        return response()->json($products);
    }

    public function show(Product $product): JsonResponse
    {
        if (! $product->is_active) {
            abort(404);
        }

        return response()->json([
            'product' => $product->load(['category', 'images']),
        ]);
    }
}
