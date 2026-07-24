<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = Cache::remember('categories:active', now()->addMinutes(30), function () {
            return Category::where('is_active', true)->orderBy('name')->get()->toArray();
        });

        return response()->json([
            'categories' => $categories,
        ]);
    }
}
