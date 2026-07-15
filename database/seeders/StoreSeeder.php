<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Expedition;
use App\Models\Banner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
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
            ['name' => 'Tiki',         'code' => 'tiki',     'service' => 'REG', 'base_cost' => 11000, 'estimated_days' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ], ['code'], ['name', 'service', 'base_cost', 'estimated_days', 'is_active', 'updated_at']);

        // ============================
        // BANNERS CAROUSEL
        // ============================
        Banner::query()->delete();

        $availableBanners = [
            [
                'title' => 'OUR BEST FUTURE',
                'description' => 'OUR BEST FUTURE UBSI',
                'file' => 'assets/img/title1.jpg',
                'order' => 1
            ],
            [
                'title' => 'Ormik 2025',
                'description' => 'Ormik 2025',
                'file' => 'assets/img/title2.png',
                'order' => 2
            ],
            [
                'title' => 'HUT UBSI 2026',
                'description' => 'HUT UBSI 2026',
                'file' => 'assets/img/title3.png',
                'order' => 3
            ]
        ];

        Storage::disk('public')->makeDirectory('banners');

        foreach ($availableBanners as $bData) {
            $filename = basename($bData['file']);
            $destPath = 'banners/' . Str::slug(pathinfo($filename, PATHINFO_FILENAME)) . '.' . pathinfo($filename, PATHINFO_EXTENSION);
            $sourcePath = public_path($bData['file']);

            if (file_exists($sourcePath)) {
                if (!Storage::disk('public')->exists($destPath)) {
                    Storage::disk('public')->put($destPath, file_get_contents($sourcePath));
                }

                Banner::create([
                    'image_path' => $destPath,
                    'title' => $bData['title'],
                    'description' => $bData['description'],
                    'order' => $bData['order'],
                ]);
            }
        }

        $this->command->info('✅ StoreSeeder selesai (Kategori, Ekspeditur & Banner Carousel).');
    }
}
