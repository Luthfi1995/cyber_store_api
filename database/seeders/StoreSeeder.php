<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expedition;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StoreSeeder extends Seeder
{
    public function run(): void
    {
        // ============================
        // CATEGORIES
        // ============================
        $categoryNames = ['Topi', 'Baju', 'Tumbler'];
        foreach ($categoryNames as $name) {
            Category::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'description' => 'Kategori Resmi ' . $name, 'is_active' => true]
            );
        }

        // ============================
        // EXPEDITIONS
        // ============================
        Expedition::upsert([
            ['name' => 'JNE Regular',  'code' => 'jne_reg',  'service' => 'REG', 'base_cost' => 15000, 'estimated_days' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'J&T Express',  'code' => 'jnt',      'service' => 'EZ',  'base_cost' => 14000, 'estimated_days' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'SiCepat',      'code' => 'sicepat',  'service' => 'REG', 'base_cost' => 13000, 'estimated_days' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Anteraja',     'code' => 'anteraja', 'service' => 'REG', 'base_cost' => 12000, 'estimated_days' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code'], ['name', 'service', 'base_cost', 'estimated_days', 'is_active', 'updated_at']);

        // ============================
        // PRODUCTS
        // ============================
        $topi    = Category::where('slug', 'topi')->first();
        $baju    = Category::where('slug', 'baju')->first();
        $tumbler = Category::where('slug', 'tumbler')->first();

        $products = [
            // Topi
            ['category_id' => $topi->id, 'name' => 'Topi UBSI Official',   'price' => 55000, 'original_price' => null,  'rating' => 4.9, 'reviews_count' => 124, 'sizes' => ['All Size'], 'colors' => [['name' => 'Blue', 'hex' => '#0d47a1'], ['name' => 'White', 'hex' => '#ffffff']], 'stock' => 100, 'weight' => 150, 'is_recommended' => true,  'description' => 'Topi resmi UBSI Official dengan bordir berkualitas tinggi dan bahan premium.', 'main_photo' => 'topi ubsi kuning.jpg'],
            ['category_id' => $topi->id, 'name' => 'Topi BSI Alumni',      'price' => 60000, 'original_price' => 75000, 'rating' => 4.8, 'reviews_count' => 88,  'sizes' => ['All Size'], 'colors' => [['name' => 'Black', 'hex' => '#000000'], ['name' => 'White', 'hex' => '#ffffff']], 'stock' => 80,  'weight' => 150, 'is_recommended' => false, 'description' => 'Topi spesial Alumni BSI. Sangat cocok digunakan untuk reuni maupun kegiatan sehari-hari.', 'main_photo' => 'topi ubsi merah.jpg'],
            ['category_id' => $topi->id, 'name' => 'Topi BSI Football',    'price' => 58000, 'original_price' => null,  'rating' => 4.7, 'reviews_count' => 42,  'sizes' => ['All Size'], 'colors' => [['name' => 'Blue', 'hex' => '#0000ff']], 'stock' => 120, 'weight' => 150, 'is_recommended' => false, 'description' => 'Topi sepak bola BSI. Nyaman dipakai saat berolahraga dan outdoor.', 'main_photo' => 'topi ubsi putih.jpg'],
            ['category_id' => $topi->id, 'name' => 'Topi BSI Staff',       'price' => 55000, 'original_price' => null,  'rating' => 4.6, 'reviews_count' => 18,  'sizes' => ['All Size'], 'colors' => [['name' => 'Grey', 'hex' => '#808080']], 'stock' => 50,  'weight' => 150, 'is_recommended' => false, 'description' => 'Topi resmi untuk jajaran staff BSI.', 'main_photo' => 'topi ubsi kuning.jpg'],
            ['category_id' => $topi->id, 'name' => 'Topi BSI Minimalist',  'price' => 52000, 'original_price' => null,  'rating' => 4.8, 'reviews_count' => 64,  'sizes' => ['All Size'], 'colors' => [['name' => 'White', 'hex' => '#ffffff']], 'stock' => 90,  'weight' => 150, 'is_recommended' => false, 'description' => 'Topi bergaya minimalis dengan cetakan logo BSI kecil yang elegan.', 'main_photo' => 'topi ubsi putih.jpg'],
            ['category_id' => $topi->id, 'name' => 'Topi BSI Vintage',     'price' => 62000, 'original_price' => null,  'rating' => 4.9, 'reviews_count' => 31,  'sizes' => ['All Size'], 'colors' => [['name' => 'Navy Denim', 'hex' => '#1a237e']], 'stock' => 70, 'weight' => 150, 'is_recommended' => false, 'description' => 'Topi bergaya klasik vintage denim dengan logo BSI bordir retro.', 'main_photo' => 'topi ubsi merah.jpg'],
            // Baju
            ['category_id' => $baju->id, 'name' => 'Baju Kaos BSI University - Official Merch', 'price' => 85000, 'original_price' => 120000, 'rating' => 4.8, 'reviews_count' => 112, 'sizes' => ['S', 'M', 'L', 'XL'], 'colors' => [['name' => 'Green', 'hex' => '#2e7d32'], ['name' => 'Black', 'hex' => '#000000'], ['name' => 'Red', 'hex' => '#d32f2f']], 'stock' => 150, 'weight' => 250, 'is_recommended' => true,  'description' => 'Baju Kaos BSI University Official Merch. Material premium, fit nyaman.', 'main_photo' => 'baju ubsi hijau.jpg'],
            ['category_id' => $baju->id, 'name' => 'Baju Kaos UBSI',       'price' => 85000, 'original_price' => null,  'rating' => 4.8, 'reviews_count' => 95,  'sizes' => ['S', 'M', 'L', 'XL'], 'colors' => [['name' => 'Green', 'hex' => '#2e7d32']], 'stock' => 200, 'weight' => 250, 'is_recommended' => true,  'description' => 'Baju kaos berkualitas tinggi berlogo UBSI dengan warna hijau khas.', 'main_photo' => 'baju ubsi putih.jpg'],
            ['category_id' => $baju->id, 'name' => 'Hoodie BSI Oversized', 'price' => 145000, 'original_price' => 175000, 'rating' => 4.9, 'reviews_count' => 67, 'sizes' => ['S', 'M', 'L', 'XL', 'XXL'], 'colors' => [['name' => 'Dark Navy', 'hex' => '#0a1628'], ['name' => 'Cream', 'hex' => '#f5f0e8']], 'stock' => 8, 'weight' => 500, 'is_recommended' => true, 'description' => 'Hoodie oversize premium BSI. Bahan fleece tebal hangat, cocok untuk musim hujan.', 'main_photo' => 'baju ubsi kuning.jpg'],
            // Tumbler
            ['category_id' => $tumbler->id, 'name' => 'Tumbler UBSI',      'price' => 45000, 'original_price' => null,  'rating' => 4.9, 'reviews_count' => 74,  'sizes' => ['Standard'], 'colors' => [['name' => 'Silver', 'hex' => '#c0c0c0'], ['name' => 'Black', 'hex' => '#000000']], 'stock' => 60, 'weight' => 400, 'is_recommended' => true, 'description' => 'Tumbler stainless steel penjaga suhu panas/dingin berlogo UBSI.', 'main_photo' => 'tumbler ubsi.jpg'],
            ['category_id' => $tumbler->id, 'name' => 'Tumbler BSI Premium Glass', 'price' => 75000, 'original_price' => 95000, 'rating' => 4.8, 'reviews_count' => 38, 'sizes' => ['500ml'], 'colors' => [['name' => 'Clear', 'hex' => '#e8f4f8'], ['name' => 'Teal', 'hex' => '#00796b']], 'stock' => 3, 'weight' => 350, 'is_recommended' => false, 'description' => 'Tumbler kaca borosilikat BSI. Ramah lingkungan, elegan, dan tahan lama.', 'main_photo' => 'tumbler ubsi hitam.jpg'],
        ];

        foreach ($products as $pData) {
            Product::firstOrCreate(
                ['slug' => Str::slug($pData['name'])],
                [
                    'category_id'    => $pData['category_id'],
                    'name'           => $pData['name'],
                    'sku'            => strtoupper(Str::random(8)),
                    'price'          => $pData['price'],
                    'original_price' => $pData['original_price'],
                    'rating'         => $pData['rating'],
                    'reviews_count'  => $pData['reviews_count'],
                    'sizes'          => $pData['sizes'],
                    'colors'         => $pData['colors'],
                    'stock'          => $pData['stock'],
                    'weight'         => $pData['weight'],
                    'is_recommended' => $pData['is_recommended'],
                    'description'    => $pData['description'],
                    'main_photo'     => $pData['main_photo'] ?? null,
                    'is_active'      => true,
                ]
            );
        }

        $this->command->info('✅ StoreSeeder selesai:');
        $this->command->info('   👤 Users: 1 Superadmin, 2 Admin, ' . count($customers) . ' Customer');
        $this->command->info('   📦 Products: ' . count($products));
        $this->command->info('   🏷️  Categories: ' . count($categoryNames));
        $this->command->info('   🚚 Expeditions: 4');
    }
}
