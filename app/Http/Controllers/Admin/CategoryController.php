<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $categories = $query->latest()->paginate(15)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        $slug = Str::slug($validated['name']);
        $base = $slug;
        $i    = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i++;
        }

        Category::create([
            'name'        => $validated['name'],
            'slug'        => $slug,
            'description' => $validated['description'] ?? '',
            'is_active'   => $request->boolean('is_active', true),
        ]);

        // Hapus cache kategori agar Flutter app mendapat data terbaru
        Cache::forget('categories:active');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active'   => ['boolean'],
        ]);

        if ($category->name !== $validated['name']) {
            $slug = Str::slug($validated['name']);
            $base = $slug;
            $i    = 1;
            while (Category::where('slug', $slug)->where('id', '!=', $category->id)->exists()) {
                $slug = $base . '-' . $i++;
            }
            $category->slug = $slug;
        }

        $category->update([
            'name'        => $validated['name'],
            'slug'        => $category->slug,
            'description' => $validated['description'] ?? '',
            'is_active'   => $request->boolean('is_active'),
        ]);

        // Hapus cache kategori agar Flutter app mendapat data terbaru
        Cache::forget('categories:active');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', 'Kategori tidak dapat dihapus karena masih memiliki produk.');
        }

        $category->delete();

        // Hapus cache kategori agar Flutter app mendapat data terbaru
        Cache::forget('categories:active');

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori berhasil dihapus.');
    }

    public function toggleActive(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        // Hapus cache kategori agar Flutter app mendapat data terbaru
        Cache::forget('categories:active');

        $status = $category->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kategori {$category->name} berhasil {$status}.");
    }
}
