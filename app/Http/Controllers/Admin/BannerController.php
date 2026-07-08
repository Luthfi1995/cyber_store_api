<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class BannerController extends Controller
{
    /**
     * Resize & crop gambar menggunakan PHP GD bawaan (tanpa library tambahan).
     * Target: 1200 × 400 px (crop center).
     */
    private function resizeAndSave(string $absolutePath): void
    {
        $info = @getimagesize($absolutePath);
        if (!$info) return;

        [$srcW, $srcH, $type] = $info;
        $targetW = 1200;
        $targetH = 400;

        // Buat sumber
        $src = match ($type) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($absolutePath),
            IMAGETYPE_PNG  => imagecreatefrompng($absolutePath),
            IMAGETYPE_WEBP => imagecreatefromwebp($absolutePath),
            default        => null,
        };
        if (!$src) return;

        // Hitung crop center (cover / fit)
        $srcRatio    = $srcW / $srcH;
        $targetRatio = $targetW / $targetH;

        if ($srcRatio > $targetRatio) {
            // Lebih lebar → crop kiri-kanan
            $cropH = $srcH;
            $cropW = (int) round($srcH * $targetRatio);
            $cropX = (int) round(($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            // Lebih tinggi → crop atas-bawah
            $cropW = $srcW;
            $cropH = (int) round($srcW / $targetRatio);
            $cropX = 0;
            $cropY = (int) round(($srcH - $cropH) / 2);
        }

        $dst = imagecreatetruecolor($targetW, $targetH);

        // Pertahankan transparansi PNG
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }

        imagecopyresampled($dst, $src, 0, 0, $cropX, $cropY, $targetW, $targetH, $cropW, $cropH);

        // Simpan dengan format yang sama
        match ($type) {
            IMAGETYPE_JPEG => imagejpeg($dst, $absolutePath, 85),
            IMAGETYPE_PNG  => imagepng($dst, $absolutePath, 8),
            IMAGETYPE_WEBP => imagewebp($dst, $absolutePath, 85),
            default        => null,
        };

        imagedestroy($src);
        imagedestroy($dst);
    }

    /**
     * Display a listing of the banners.
     */
    public function index()
    {
        $banners = Banner::ordered()->paginate(10);
        return view('admin.banners.index', compact('banners'));
    }

    /**
     * Show the form for creating a new banner.
     */
    public function create()
    {
        return view('admin.banners.create');
    }

    /**
     * Store a newly created banner.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'order'       => 'nullable|integer',
            'image'       => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        $path = $request->file('image')->store('banners', 'public');
        $this->resizeAndSave(Storage::disk('public')->path($path));

        $data['image_path'] = $path;
        Banner::create($data);

        // Hapus cache banner agar API menampilkan data terbaru
        Cache::store('redis')->forget('banners:active');

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified banner.
     */
    public function edit(Banner $banner)
    {
        return view('admin.banners.edit', compact('banner'));
    }

    /**
     * Update the specified banner.
     */
    public function update(Request $request, Banner $banner)
    {
        $data = $request->validate([
            'title'       => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'order'       => 'nullable|integer',
            'image'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:5120',
        ]);

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image_path);
            $path = $request->file('image')->store('banners', 'public');
            $this->resizeAndSave(Storage::disk('public')->path($path));
            $data['image_path'] = $path;
        }

        $banner->update($data);

        // Hapus cache banner agar API menampilkan data terbaru
        Cache::store('redis')->forget('banners:active');

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil diperbarui.');
    }

    /**
     * Remove the specified banner.
     */
    public function destroy(Banner $banner)
    {
        Storage::disk('public')->delete($banner->image_path);
        $banner->delete();

        // Hapus cache banner agar API menampilkan data terbaru
        Cache::store('redis')->forget('banners:active');

        return redirect()->route('admin.banners.index')->with('success', 'Banner berhasil dihapus.');
    }
}
