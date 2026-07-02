<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
class ProductController extends Controller
{
    public function index(Request $request)
    {
        // Buat cache key berdasarkan parameter request
        $cacheKey = 'admin:products:index:page_' . $request->input('page', 1) .
            ':search_' . md5($request->input('search', '')) .
            ':category_' . $request->input('category', '') .
            ':status_' . $request->input('status', '');

        $products = Cache::store('redis')
            ->tags(['admin-products'])
            ->remember($cacheKey, now()->addHours(6), function () use ($request) {
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

                return $query->latest()->paginate(15)->withQueryString();
            });

        $categories = Category::query()->where('is_active', true)->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::query()->where('is_active', true)->get();
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
            'sizes'          => ['nullable'],
            'colors'         => ['nullable'],
            'is_active'      => ['boolean'],
            'is_recommended' => ['boolean'],
            'main_photo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_2'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_3'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $slug = Str::slug($validated['name']);
        $baseSlug = $slug;
        $counter = 1;
        while (Product::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        // Parse sizes & colors dari input form (array atau comma-separated string)
        $sizes = [];
        if (!empty($validated['sizes'])) {
            $sizeInput = is_array($validated['sizes']) ? $validated['sizes'] : explode(',', $validated['sizes']);
            $sizes = array_filter(array_map(function ($item) {
                return is_string($item) ? trim($item) : (is_array($item) ? ($item['name'] ?? '') : '');
            }, $sizeInput));
        }

        $colors = [];
        if (!empty($validated['colors'])) {
            $colorInput = is_array($validated['colors']) ? $validated['colors'] : explode(',', $validated['colors']);
            foreach ($colorInput as $item) {
                if (is_string($item)) {
                    $colorName = trim($item);
                    if ($colorName !== '') {
                        $colors[] = ['name' => $colorName, 'hex' => '#000000'];
                    }
                } elseif (is_array($item)) {
                    $colorName = trim($item['name'] ?? '');
                    $colorHex = $item['hex'] ?? '#000000';
                    if ($colorName !== '') {
                        $colors[] = ['name' => $colorName, 'hex' => $colorHex];
                    }
                }
            }
        }

        $mainPhotoPath = null;
        if ($request->hasFile('main_photo')) {
            $mainPhotoPath = $request->file('main_photo')->store('products', 'public');
        }

        $product = Product::create([
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

        if ($request->hasFile('photo_2')) {
            $path2 = $request->file('photo_2')->store('products', 'public');
            $product->images()->create([
                'image' => $path2,
                'sort_order' => 1,
            ]);
        }

        if ($request->hasFile('photo_3')) {
            $path3 = $request->file('photo_3')->store('products', 'public');
            $product->images()->create([
                'image' => $path3,
                'sort_order' => 2,
            ]);
        }

        Product::clearCache($product);
        // Flush cache tag untuk list produk admin
        Cache::store('redis')->tags(['admin-products'])->flush();

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit(Product $product)
    {
        $categories = Category::query()->where('is_active', true)->get();
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
            'sizes'          => ['nullable'],
            'colors'         => ['nullable'],
            'is_active'      => ['boolean'],
            'is_recommended' => ['boolean'],
            'main_photo'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_2'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'photo_3'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'remove_photo_2' => ['nullable', 'boolean'],
            'remove_photo_3' => ['nullable', 'boolean'],
        ]);

        // Update slug hanya jika nama berubah
        $slug = $product->slug;
        if ($product->name !== $validated['name']) {
            $slug = Str::slug($validated['name']);
            $baseSlug = $slug;
            $counter = 1;
            while (Product::query()->where('slug', $slug)->where('id', '!=', $product->id)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }
        }

        // Parse sizes & colors dari input form (array atau comma-separated string)
        $sizes = [];
        if (!empty($validated['sizes'])) {
            $sizeInput = is_array($validated['sizes']) ? $validated['sizes'] : explode(',', $validated['sizes']);
            $sizes = array_filter(array_map(function ($item) {
                return is_string($item) ? trim($item) : (is_array($item) ? ($item['name'] ?? '') : '');
            }, $sizeInput));
        }

        $colors = [];
        if (!empty($validated['colors'])) {
            $colorInput = is_array($validated['colors']) ? $validated['colors'] : explode(',', $validated['colors']);
            foreach ($colorInput as $item) {
                if (is_string($item)) {
                    $colorName = trim($item);
                    if ($colorName !== '') {
                        $colors[] = ['name' => $colorName, 'hex' => '#000000'];
                    }
                } elseif (is_array($item)) {
                    $colorName = trim($item['name'] ?? '');
                    $colorHex = $item['hex'] ?? '#000000';
                    if ($colorName !== '') {
                        $colors[] = ['name' => $colorName, 'hex' => $colorHex];
                    }
                }
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
        // Flush cache tag untuk list produk admin setelah update
        Cache::store('redis')->tags(['admin-products'])->flush();

        // Handle Photo 2
        if ($request->boolean('remove_photo_2')) {
            $oldImage = $product->images()->where('sort_order', 1)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }
        } elseif ($request->hasFile('photo_2')) {
            $oldImage = $product->images()->where('sort_order', 1)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }

            $path2 = $request->file('photo_2')->store('products', 'public');
            $product->images()->create([
                'image' => $path2,
                'sort_order' => 1,
            ]);
        }

        // Handle Photo 3
        if ($request->boolean('remove_photo_3')) {
            $oldImage = $product->images()->where('sort_order', 2)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }
        } elseif ($request->hasFile('photo_3')) {
            $oldImage = $product->images()->where('sort_order', 2)->first();
            if ($oldImage) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldImage->image);
                $oldImage->delete();
            }

            $path3 = $request->file('photo_3')->store('products', 'public');
            $product->images()->create([
                'image' => $path3,
                'sort_order' => 2,
            ]);
        }

        Product::clearCache($product);

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            return back()->with('error', 'Produk tidak dapat dihapus karena sudah memiliki riwayat transaksi/order. Anda dapat menonaktifkan produk ini saja.');
        }

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