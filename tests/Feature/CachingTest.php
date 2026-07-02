<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CachingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Gunakan sqlite in-memory agar tes berjalan tanpa MySQL
        config(['database.default' => 'sqlite']);
        config(['database.connections.sqlite.database' => ':memory:']);
        
        // Ubah driver cache 'redis' menjadi 'array' agar tes berjalan tanpa koneksi Redis asli
        config(['cache.stores.redis.driver' => 'array']);

        $this->artisan('migrate');
    }

    public function test_categories_are_cached_forever()
    {
        // Setup data
        Category::factory()->create(['name' => 'Buku', 'is_active' => true]);
        Category::factory()->create(['name' => 'Pakaian', 'is_active' => true]);

        // Pastikan cache kosong sebelum request pertama
        $this->assertFalse(Cache::store('redis')->has('categories:active'));

        // Request pertama
        $response1 = $this->getJson('/api/v1/categories');
        $response1->assertStatus(200);

        // Pastikan sekarang data sudah di-cache
        $this->assertTrue(Cache::store('redis')->has('categories:active'));
        $cachedData = Cache::store('redis')->get('categories:active');
        $this->assertCount(2, $cachedData);

        // Buat kategori baru di DB (tidak akan muncul di request kedua jika cache bekerja)
        Category::factory()->create(['name' => 'Elektronik', 'is_active' => true]);

        $response2 = $this->getJson('/api/v1/categories');
        $response2->assertStatus(200);
        // Jumlah kategori di response masih 2 karena membaca dari cache
        $this->assertCount(2, $response2->json('categories'));
    }

    public function test_categories_cache_is_invalidated_on_save_or_delete()
    {
        $category = Category::factory()->create(['name' => 'Buku', 'is_active' => true]);

        // Isi cache
        $this->getJson('/api/v1/categories');
        $this->assertTrue(Cache::store('redis')->has('categories:active'));

        // Update kategori (harus menghapus cache)
        $category->update(['name' => 'Buku Cetak']);
        $this->assertFalse(Cache::store('redis')->has('categories:active'));

        // Isi cache lagi
        $this->getJson('/api/v1/categories');
        $this->assertTrue(Cache::store('redis')->has('categories:active'));

        // Hapus kategori (harus menghapus cache)
        $category->delete();
        $this->assertFalse(Cache::store('redis')->has('categories:active'));
    }

    public function test_products_list_and_details_are_cached()
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Sepatu Keren',
            'is_active' => true,
        ]);

        // 1. Uji Caching List Produk
        $responseList1 = $this->getJson('/api/v1/products');
        $responseList1->assertStatus(200);

        // Cari cache key dinamis
        $cacheKey = "products:index:page_1:per_page_12:cat_:search_:rec_";
        $this->assertTrue(Cache::store('redis')->tags(['products-list'])->has($cacheKey));

        // 2. Uji Caching Detail Produk
        $responseDetail1 = $this->getJson("/api/v1/products/{$product->id}");
        $responseDetail1->assertStatus(200);

        $this->assertTrue(Cache::store('redis')->has("product:detail:{$product->id}"));
    }

    public function test_products_cache_is_invalidated_on_save_or_delete()
    {
        $category = Category::factory()->create(['is_active' => true]);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Sepatu Keren',
            'is_active' => true,
        ]);

        // Isi cache
        $this->getJson('/api/v1/products');
        $this->getJson("/api/v1/products/{$product->id}");

        $cacheKey = "products:index:page_1:per_page_12:cat_:search_:rec_";
        $this->assertTrue(Cache::store('redis')->tags(['products-list'])->has($cacheKey));
        $this->assertTrue(Cache::store('redis')->has("product:detail:{$product->id}"));

        // Update produk (harus menghapus cache detail dan list)
        $product->update(['price' => 150000]);

        $this->assertFalse(Cache::store('redis')->tags(['products-list'])->has($cacheKey));
        $this->assertFalse(Cache::store('redis')->has("product:detail:{$product->id}"));
    }
}
