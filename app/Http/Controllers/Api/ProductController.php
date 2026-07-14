<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $page = $request->input('page', 1);
        $perPage = $request->integer('per_page', 12);
        $categoryId = $request->input('category_id', '');
        $search = $request->input('search', '');
        $isRecommended = $request->has('is_recommended') ? $request->boolean('is_recommended') : '';

        $searchHash = $search ? md5($search) : '';
        $cacheKey = "products:index:page_{$page}:per_page_{$perPage}:cat_{$categoryId}:search_{$searchHash}:rec_{$isRecommended}";

        /** @var CacheRepository $cache */
        $cache = Cache::store('redis');

        $products = $cache->tags(['products-list'])->remember($cacheKey, now()->addHours(12), function () use ($request) {
            return Product::query()
                ->with(['category', 'images'])
                ->where('is_active', true)
                ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->category_id))
                ->when($request->filled('search'), fn ($query) => $query->where('name', 'like', '%' . $request->search . '%'))
                ->when($request->has('is_recommended'), fn ($query) => $query->where('is_recommended', $request->boolean('is_recommended')))
                ->latest()
                ->paginate($request->integer('per_page', 12))
                ->toArray();
        });

        return response()->json($products);
    }

    public function show($id): JsonResponse
    {
        $product = Cache::store('redis')->remember("product:detail:{$id}", now()->addHours(24), function () use ($id) {
            $prod = Product::with(['category', 'images'])->find($id);
            if (! $prod || ! $prod->is_active) {
                return null;
            }
            return $prod->toArray();
        });

        if (! $product) {
            abort(404);
        }

        return response()->json([
            'product' => $product,
        ]);
    }
}
