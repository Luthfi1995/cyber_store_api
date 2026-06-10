<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products   = $query->latest()->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'    => ['required', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:200'],
            'sku'            => ['nullable', 'string', 'max:50', 'unique:products,sku'],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'stock'          => ['required', 'integer', 'min:0'],
            'weight'         => ['required', 'integer', 'min:1'],
            'sizes'          => ['nullable', 'string'],
            'colors'         => ['nullable', 'string'],
            'is_active'      => ['boolean'],
            'is_recommended' => ['boolean'],
            'main_photo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Parse sizes & colors dari comma-separated string
        $sizes = [];
        if (!empty($validated['sizes'])) {
            $sizes = array_map('trim', explode(',', $validated['sizes']));
            $sizes = array_filter($sizes);
        }

        $colors = [];
        if (!empty($validated['colors'])) {
            $colorInput = array_map('trim', explode(',', $validated['colors']));
            foreach (array_filter($colorInput) as $color) {
                $colors[] = ['name' => $color, 'hex' => '#000000'];
            }
        }

        $mainPhotoPath = null;
        if ($request->hasFile('main_photo')) {
            $mainPhotoPath = $request->file('main_photo')->store('products', 'public');
        }

        Product::create([
            'category_id'    => $validated['category_id'],
            'name'           => $validated['name'],
            'slug'           => $slug,
            'sku'            => $validated['sku'] ?? strtoupper(Str::random(8)),
            'description'    => $validated['description'] ?? '',
            'price'          => $validated['price'],
            'original_price' => $validated['original_price'] ?? null,
            'stock'          => $validated['stock'],
            'weight'         => $validated['weight'],
            'sizes'          => array_values($sizes),
            'colors'         => $colors,
            'is_active'      => $request->boolean('is_active', true),
            'is_recommended' => $request->boolean('is_recommended', false),
            'main_photo'     => $mainPhotoPath,
            'rating'         => 4.8,
            'reviews_count'  => 0,
        ]);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id'    => ['required', 'exists:categories,id'],
            'name'           => ['required', 'string', 'max:200'],
            'sku'            => ['nullable', 'string', 'max:50', \Illuminate\Validation\Rule::unique('products', 'sku')->ignore($product->id)],
            'description'    => ['nullable', 'string'],
            'price'          => ['required', 'numeric', 'min:0'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'stock'          => ['required', 'integer', 'min:0'],
            'weight'         => ['required', 'integer', 'min:1'],
            'sizes'          => ['nullable', 'string'],
            'colors'         => ['nullable', 'string'],
            'is_active'      => ['boolean'],
            'is_recommended' => ['boolean'],
            'main_photo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        // Update slug hanya jika nama berubah
        $slug = $product->slug;
        if ($product->name !== $validated['name']) {
            $slug = Str::slug($validated['name']);
            $baseSlug = $slug;
            $counter = 1;
            while (Product::where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
        }

        $sizes = [];
        if (!empty($validated['sizes'])) {
            $sizes = array_map('trim', explode(',', $validated['sizes']));
            $sizes = array_filter($sizes);
        }

        $colors = [];
        if (!empty($validated['colors'])) {
            $colorInput = array_map('trim', explode(',', $validated['colors']));
            foreach (array_filter($colorInput) as $color) {
                $colors[] = ['name' => $color, 'hex' => '#000000'];
            }
        }

        $data = [
            'category_id'    => $validated['category_id'],
            'name'           => $validated['name'],
            'slug'           => $slug,
            'sku'            => $validated['sku'] ?? $product->sku,
            'description'    => $validated['description'] ?? '',
            'price'          => $validated['price'],
            'original_price' => $validated['original_price'] ?? null,
            'stock'          => $validated['stock'],
            'weight'         => $validated['weight'],
            'sizes'          => array_values($sizes),
            'colors'         => $colors,
            'is_active'      => $request->boolean('is_active'),
            'is_recommended' => $request->boolean('is_recommended'),
        ];

        if ($request->hasFile('main_photo')) {
            if ($product->main_photo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($product->main_photo);
            }
            $data['main_photo'] = $request->file('main_photo')->store('products', 'public');
        }

        if ($request->boolean('remove_main_photo') && $product->main_photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->main_photo);
            $data['main_photo'] = null;
        }

        $product->update($data);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->main_photo) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($product->main_photo);
        }
        $product->delete();
        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);
        $status = $product->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Produk {$product->name} berhasil {$status}.");
    }
}
