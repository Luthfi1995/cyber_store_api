<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Mengembalikan daftar banner aktif, diurutkan berdasarkan field `order`.
     * Data di-cache di Redis selama 1 jam untuk mengurangi query DB.
     */
    public function index()
    {
        $banners = Cache::remember('banners:active', now()->addHour(), function () {
            return Banner::ordered()->get()->map(function ($banner) {
                return [
                    'id'          => $banner->id,
                    'title'       => $banner->title,
                    'description' => $banner->description,
                    'order'       => $banner->order,
                    'image_url'   => $banner->image_path
                        ? Storage::disk('public')->url($banner->image_path)
                        : null,
                ];
            })->toArray();
        });

        return response()->json(['banners' => $banners]);
    }
}
