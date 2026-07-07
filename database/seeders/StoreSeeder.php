<?php
 
namespace Database\Seeders;
 
use App\Models\Category;
use App\Models\Expedition;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
 
class StoreSeeder extends Seeder
{
    /**
     * Copy a local image from public/assets/img to storage and return its path.
     */
    private function getLocalPhoto(string $sourceFilename): ?string
    {
        $sourcePath = public_path('assets/img/' . $sourceFilename);
        $destPath = 'products/' . Str::slug(pathinfo($sourceFilename, PATHINFO_FILENAME)) . '.' . pathinfo($sourceFilename, PATHINFO_EXTENSION);

        if (!Storage::disk('public')->exists($destPath) && file_exists($sourcePath)) {
            Storage::disk('public')->put($destPath, file_get_contents($sourcePath));
        }

        return file_exists($sourcePath) ? $destPath : null;
    }

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
        // SETUP LOCAL PHOTOS
        // ============================
        $this->command->info('📸 Menyiapkan foto produk dari local storage...');
        Storage::disk('public')->makeDirectory('products');

        $availableTopi = [
            'topi ubsi kuning.jpg',
            'topi ubsi kuning1.jpg',
            'topi ubsi merah.jpg',
            'topi ubsi merah2.jpg',
            'topi ubsi putih.jpg',
            'topi ubsi putih1.jpg',
        ];
        $topiPhotos = [];
        for ($i = 0; $i < 20; $i++) {
            $topiPhotos[] = $this->getLocalPhoto($availableTopi[$i % count($availableTopi)]);
        }

        $availableBaju = [
            'baju ubsi hijau.jpg',
            'baju ubsi kuning.jpg',
            'baju ubsi putih.jpg',
        ];
        $bajuPhotos = [];
        for ($i = 0; $i < 25; $i++) {
            $bajuPhotos[] = $this->getLocalPhoto($availableBaju[$i % count($availableBaju)]);
        }

        $availableTumbler = [
            'tumbler ubsi biru.jpg',
            'tumbler ubsi hitam.jpg',
            'tumbler ubsi.jpg',
        ];
        $tumblerPhotos = [];
        for ($i = 0; $i < 18; $i++) {
            $tumblerPhotos[] = $this->getLocalPhoto($availableTumbler[$i % count($availableTumbler)]);
        }

        $this->command->info('   ✅ Foto berhasil disiapkan.');

        // ============================
        // PRODUCTS
        // ============================
        $topi    = Category::where('slug', 'topi')->first();
        $baju    = Category::where('slug', 'baju')->first();
        $tumbler = Category::where('slug', 'tumbler')->first();
 
        $products = [
 
            // =========================================================
            // TOPI (20 produk)
            // =========================================================
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[0],  'name' => 'Topi UBSI Ormik & Semot 2026 - Kuning Official',         'price' => 55000,  'original_price' => null,   'rating' => 4.5, 'reviews_count' => 120, 'sizes' => ['All Size'], 'colors' => [['name' => 'Yellow',      'hex' => '#EEFF00']], 'stock' => 100, 'weight' => 150, 'is_recommended' => true,  'description' => 'Topi resmi UBSI Official bordir premium warna kuning cerah. Perlengkapan wajib OSPEK 2026/2027.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[1],  'name' => 'Topi UBSI Ormik & Semot 2026 - Merah Official',          'price' => 60000,  'original_price' => 75000,  'rating' => 4.3, 'reviews_count' => 87,  'sizes' => ['All Size'], 'colors' => [['name' => 'Red',         'hex' => '#FC0404']], 'stock' => 80,  'weight' => 150, 'is_recommended' => false, 'description' => 'Topi UBSI merah dengan bordir logo kampus, cocok untuk acara resmi kampus.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[2],  'name' => 'Topi UBSI Ormik & Semot 2026 - Putih Official',          'price' => 58000,  'original_price' => null,   'rating' => 4.1, 'reviews_count' => 65,  'sizes' => ['All Size'], 'colors' => [['name' => 'White',       'hex' => '#FFFFFF']], 'stock' => 120, 'weight' => 150, 'is_recommended' => false, 'description' => 'Topi putih bersih berlogo UBSI, nyaman dipakai seharian di lingkungan kampus.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[3],  'name' => 'Topi UBSI Bordir Premium - Navy Blue',                   'price' => 65000,  'original_price' => 80000,  'rating' => 4.7, 'reviews_count' => 200, 'sizes' => ['All Size'], 'colors' => [['name' => 'Navy',        'hex' => '#001F5B']], 'stock' => 75,  'weight' => 160, 'is_recommended' => true,  'description' => 'Topi navy blue premium dengan sulam emas logo UBSI, tampil elegan di setiap kesempatan.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[4],  'name' => 'Topi UBSI Snapback Classic - Hitam',                     'price' => 70000,  'original_price' => null,   'rating' => 4.2, 'reviews_count' => 53,  'sizes' => ['All Size'], 'colors' => [['name' => 'Black',       'hex' => '#000000']], 'stock' => 50,  'weight' => 170, 'is_recommended' => false, 'description' => 'Snapback hitam klasik berlogo UBSI, desain minimalis cocok untuk aktivitas sehari-hari.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[5],  'name' => 'Topi UBSI Bucket Hat - Hijau Army',                      'price' => 72000,  'original_price' => 90000,  'rating' => 4.6, 'reviews_count' => 145, 'sizes' => ['All Size'], 'colors' => [['name' => 'Army Green', 'hex' => '#4B5320']], 'stock' => 40,  'weight' => 140, 'is_recommended' => true,  'description' => 'Bucket hat gaya kekinian warna hijau army, cocok untuk outing dan kegiatan outdoor kampus.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[6],  'name' => 'Topi UBSI Trucker Cap - Abu-abu',                        'price' => 62000,  'original_price' => null,   'rating' => 3.9, 'reviews_count' => 38,  'sizes' => ['All Size'], 'colors' => [['name' => 'Grey',        'hex' => '#9E9E9E']], 'stock' => 60,  'weight' => 155, 'is_recommended' => false, 'description' => 'Trucker cap abu-abu berlogo UBSI di bagian depan, sirkulasi udara baik dengan bahan jaring.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[7],  'name' => 'Topi UBSI 5 Panel Camp - Krem',                          'price' => 67000,  'original_price' => 79000,  'rating' => 4.4, 'reviews_count' => 92,  'sizes' => ['All Size'], 'colors' => [['name' => 'Cream',       'hex' => '#F5F0E8']], 'stock' => 35,  'weight' => 145, 'is_recommended' => false, 'description' => 'Topi 5 panel krem aesthetic, cocok dipadupadankan dengan outfit kasual kampus.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[8],  'name' => 'Topi UBSI Baseball Cap - Maroon',                        'price' => 59000,  'original_price' => null,   'rating' => 4.0, 'reviews_count' => 74,  'sizes' => ['All Size'], 'colors' => [['name' => 'Maroon',     'hex' => '#800000']], 'stock' => 90,  'weight' => 150, 'is_recommended' => false, 'description' => 'Baseball cap maroon elegan dengan patch bordir UBSI, bahan twill premium.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[9],  'name' => 'Topi UBSI Dad Hat Vintage - Olive',                      'price' => 74000,  'original_price' => 88000,  'rating' => 4.8, 'reviews_count' => 310, 'sizes' => ['All Size'], 'colors' => [['name' => 'Olive',       'hex' => '#808000']], 'stock' => 25,  'weight' => 148, 'is_recommended' => true,  'description' => 'Dad hat olive vintage terlaris dengan teks UBSI retro. Best seller semester ini!'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[10], 'name' => 'Topi UBSI Flat Brim - Pink Muda',                        'price' => 63000,  'original_price' => null,   'rating' => 4.1, 'reviews_count' => 47,  'sizes' => ['All Size'], 'colors' => [['name' => 'Pink',        'hex' => '#FFB6C1']], 'stock' => 45,  'weight' => 148, 'is_recommended' => false, 'description' => 'Flat brim cap warna pink muda, cocok untuk mahasiswi yang ingin tampil cantik di kampus.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[11], 'name' => 'Topi UBSI Corduroy Cap - Coklat',                        'price' => 78000,  'original_price' => 95000,  'rating' => 4.5, 'reviews_count' => 130, 'sizes' => ['All Size'], 'colors' => [['name' => 'Brown',      'hex' => '#795548']], 'stock' => 30,  'weight' => 165, 'is_recommended' => true,  'description' => 'Corduroy cap coklat premium, tekstur unik dan tahan lama. Tambahkan kesan vintage ke penampilanmu.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[12], 'name' => 'Topi UBSI Mesh Cap Sport - Biru Elektrik',               'price' => 56000,  'original_price' => null,   'rating' => 3.8, 'reviews_count' => 29,  'sizes' => ['All Size'], 'colors' => [['name' => 'Electric Blue', 'hex' => '#0047AB']], 'stock' => 70, 'weight' => 130, 'is_recommended' => false, 'description' => 'Cap jaring sport biru elektrik, ringan dan nyaman untuk aktivitas luar ruangan.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[13], 'name' => 'Topi UBSI Washed Cap - Faded Black',                     'price' => 68000,  'original_price' => 82000,  'rating' => 4.6, 'reviews_count' => 178, 'sizes' => ['All Size'], 'colors' => [['name' => 'Faded Black', 'hex' => '#2C2C2C']], 'stock' => 55,  'weight' => 152, 'is_recommended' => true,  'description' => 'Washed cap dengan efek faded hitam yang stylish, desain bordir UBSI timbul.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[14], 'name' => 'Topi UBSI Rope Cap - Putih-Biru',                        'price' => 61000,  'original_price' => null,   'rating' => 4.2, 'reviews_count' => 61,  'sizes' => ['All Size'], 'colors' => [['name' => 'White-Blue', 'hex' => '#E3F2FD']], 'stock' => 65,  'weight' => 148, 'is_recommended' => false, 'description' => 'Rope cap dengan kombinasi putih dan biru khas UBSI, detail tali di bagian depan.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[15], 'name' => 'Topi UBSI Beanie Winter - Abu Gelap',                    'price' => 50000,  'original_price' => 65000,  'rating' => 4.3, 'reviews_count' => 99,  'sizes' => ['All Size'], 'colors' => [['name' => 'Dark Grey', 'hex' => '#424242']], 'stock' => 110, 'weight' => 100, 'is_recommended' => false, 'description' => 'Beanie rajut abu gelap berlogo UBSI, hangat dan stylish untuk musim hujan.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[16], 'name' => 'Topi UBSI Strapback Twill - Ungu',                       'price' => 64000,  'original_price' => null,   'rating' => 4.0, 'reviews_count' => 44,  'sizes' => ['All Size'], 'colors' => [['name' => 'Purple',     'hex' => '#7B1FA2']], 'stock' => 40,  'weight' => 155, 'is_recommended' => false, 'description' => 'Strapback ungu dengan bordir logo UBSI, untuk tampilan yang beda dari yang lain.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[17], 'name' => 'Topi UBSI Limited Edition Embroidered - Hitam Emas',     'price' => 95000,  'original_price' => 120000, 'rating' => 4.9, 'reviews_count' => 412, 'sizes' => ['All Size'], 'colors' => [['name' => 'Black-Gold', 'hex' => '#1A1A1A']], 'stock' => 15,  'weight' => 170, 'is_recommended' => true,  'description' => 'Limited edition! Topi eksklusif hitam dengan sulam emas mengkilap logo UBSI. Stok sangat terbatas!'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[18], 'name' => 'Topi UBSI Flat Cap Gatsby - Hitam',                      'price' => 82000,  'original_price' => 100000, 'rating' => 4.4, 'reviews_count' => 83,  'sizes' => ['All Size'], 'colors' => [['name' => 'Black',      'hex' => '#000000']], 'stock' => 20,  'weight' => 175, 'is_recommended' => false, 'description' => 'Flat cap gatsby elegan hitam premium, cocok untuk formal casual event kampus.'],
            ['category_id' => $topi->id, 'main_photo' => $topiPhotos[19], 'name' => 'Topi UBSI Topi Rimba Outdoor - Khaki',                   'price' => 88000,  'original_price' => null,   'rating' => 4.5, 'reviews_count' => 156, 'sizes' => ['All Size'], 'colors' => [['name' => 'Khaki',      'hex' => '#C3B091']], 'stock' => 28,  'weight' => 200, 'is_recommended' => true,  'description' => 'Topi rimba khaki dengan brim lebar, proteksi matahari maksimal saat kegiatan outdoor kampus.'],
 
            // =========================================================
            // BAJU (25 produk)
            // =========================================================
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[0],  'name' => 'Kaos UBSI Ormik & Semot 2026 - Hijau Official',          'price' => 85000,  'original_price' => 120000, 'rating' => 4.7, 'reviews_count' => 280, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Green',      'hex' => '#2e7d32'], ['name' => 'Black', 'hex' => '#000000']], 'stock' => 150, 'weight' => 250, 'is_recommended' => true,  'description' => 'Kaos resmi UBSI 2026 bahan cotton combed 30s, sablon waterbase berkualitas tinggi, wajib untuk OSPEK.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[1],  'name' => 'Kaos UBSI Ormik & Semot 2026 - Putih Official',          'price' => 85000,  'original_price' => null,   'rating' => 4.5, 'reviews_count' => 195, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'White',      'hex' => '#FFFFFF']], 'stock' => 200, 'weight' => 250, 'is_recommended' => true,  'description' => 'Kaos putih UBSI dengan logo kampus sablon premium, ringan dan adem dipakai seharian.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[2],  'name' => 'Kaos UBSI Ormik & Semot 2026 - Kuning Official',         'price' => 85000,  'original_price' => null,   'rating' => 4.3, 'reviews_count' => 143, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Yellow',     'hex' => '#EEFF00']], 'stock' => 175, 'weight' => 250, 'is_recommended' => false, 'description' => 'Kaos kuning cerah UBSI, bahan adem tidak mudah kusut untuk kegiatan OSPEK seharian.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[3],  'name' => 'Hoodie UBSI Oversize Premium - Navy',                    'price' => 145000, 'original_price' => 175000, 'rating' => 4.8, 'reviews_count' => 350, 'sizes' => ['S','M','L','XL','XXL'], 'colors' => [['name' => 'Navy',       'hex' => '#001F5B'], ['name' => 'Cream', 'hex' => '#F5F0E8']], 'stock' => 60, 'weight' => 500, 'is_recommended' => true,  'description' => 'Hoodie oversize premium bahan fleece tebal, sablon timbul logo UBSI, hangat dan stylish.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[4],  'name' => 'Hoodie UBSI Oversize Premium - Hitam',                   'price' => 145000, 'original_price' => 175000, 'rating' => 4.9, 'reviews_count' => 520, 'sizes' => ['S','M','L','XL','XXL'], 'colors' => [['name' => 'Black',      'hex' => '#000000']], 'stock' => 45,  'weight' => 500, 'is_recommended' => true,  'description' => 'Hoodie hitam paling laris! Bahan tebal dan hangat dengan desain UBSI eksklusif. Best seller!'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[5],  'name' => 'Kemeja UBSI Flanel Premium - Merah Kotak',               'price' => 165000, 'original_price' => 200000, 'rating' => 4.6, 'reviews_count' => 188, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Red Plaid',  'hex' => '#C62828']], 'stock' => 35,  'weight' => 400, 'is_recommended' => true,  'description' => 'Kemeja flanel kotak merah premium, bahan lembut nyaman untuk casual harian di kampus.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[6],  'name' => 'Kemeja UBSI Flanel Premium - Biru Kotak',                'price' => 165000, 'original_price' => null,   'rating' => 4.4, 'reviews_count' => 122, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Blue Plaid', 'hex' => '#1565C0']], 'stock' => 40,  'weight' => 400, 'is_recommended' => false, 'description' => 'Kemeja flanel kotak biru dengan logo UBSI subtle di dada kiri, santai namun rapi.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[7],  'name' => 'Polo Shirt UBSI Classic - Hitam',                        'price' => 110000, 'original_price' => 135000, 'rating' => 4.5, 'reviews_count' => 245, 'sizes' => ['S','M','L','XL','XXL'], 'colors' => [['name' => 'Black',      'hex' => '#000000']], 'stock' => 80,  'weight' => 300, 'is_recommended' => true,  'description' => 'Polo shirt hitam resmi UBSI dengan bordir logo, cocok untuk presentasi dan kegiatan formal kampus.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[8],  'name' => 'Polo Shirt UBSI Classic - Putih',                        'price' => 110000, 'original_price' => null,   'rating' => 4.3, 'reviews_count' => 176, 'sizes' => ['S','M','L','XL','XXL'], 'colors' => [['name' => 'White',      'hex' => '#FFFFFF']], 'stock' => 95,  'weight' => 300, 'is_recommended' => false, 'description' => 'Polo shirt putih klasik UBSI, kesan profesional untuk berbagai kegiatan kampus.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[9],  'name' => 'Polo Shirt UBSI Classic - Biru',                         'price' => 110000, 'original_price' => 130000, 'rating' => 4.4, 'reviews_count' => 168, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Blue',       'hex' => '#1976D2']], 'stock' => 70,  'weight' => 300, 'is_recommended' => false, 'description' => 'Polo biru UBSI, pilihan tepat untuk acara semi-formal dan kegiatan BEM.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[10], 'name' => 'Jaket UBSI Varsity - Hitam Emas',                        'price' => 285000, 'original_price' => 350000, 'rating' => 4.9, 'reviews_count' => 478, 'sizes' => ['S','M','L','XL','XXL'], 'colors' => [['name' => 'Black-Gold', 'hex' => '#1A1A1A']], 'stock' => 18,  'weight' => 750, 'is_recommended' => true,  'description' => 'Jaket varsity eksklusif hitam emas UBSI. Wool body + leather sleeve, patch bordir premium. Limited!'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[11], 'name' => 'Jaket UBSI Varsity - Navy Putih',                        'price' => 285000, 'original_price' => 350000, 'rating' => 4.8, 'reviews_count' => 312, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Navy-White', 'hex' => '#001F5B']], 'stock' => 22,  'weight' => 750, 'is_recommended' => true,  'description' => 'Jaket varsity navy putih UBSI, tampilan sporty elegan dengan logo kampus di dada dan punggung.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[12], 'name' => 'Sweater UBSI Crewneck - Abu Misty',                      'price' => 135000, 'original_price' => 160000, 'rating' => 4.7, 'reviews_count' => 290, 'sizes' => ['S','M','L','XL','XXL'], 'colors' => [['name' => 'Misty Grey', 'hex' => '#B0BEC5']], 'stock' => 55,  'weight' => 450, 'is_recommended' => true,  'description' => 'Crewneck abu misty dengan print UBSI di dada, bahan fleece premium tidak berbulu.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[13], 'name' => 'Sweater UBSI Crewneck - Cream',                          'price' => 135000, 'original_price' => null,   'rating' => 4.5, 'reviews_count' => 198, 'sizes' => ['S','M','L','XL','XXL'], 'colors' => [['name' => 'Cream',      'hex' => '#F5F0E8']], 'stock' => 48,  'weight' => 450, 'is_recommended' => false, 'description' => 'Crewneck cream netral, cocok dipadukan berbagai warna celana dan aksesori kampus.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[14], 'name' => 'Kaos Raglan UBSI Retro - Putih Abu',                     'price' => 95000,  'original_price' => 115000, 'rating' => 4.3, 'reviews_count' => 134, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'White-Grey', 'hex' => '#ECEFF1']], 'stock' => 85,  'weight' => 260, 'is_recommended' => false, 'description' => 'Kaos raglan kombinasi putih abu retro, desain vintage UBSI yang unik dan estetik.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[15], 'name' => 'Kaos Long Sleeve UBSI - Hitam',                          'price' => 98000,  'original_price' => null,   'rating' => 4.2, 'reviews_count' => 89,  'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Black',      'hex' => '#000000']], 'stock' => 72,  'weight' => 280, 'is_recommended' => false, 'description' => 'Kaos lengan panjang UBSI hitam, bahan spandex adem dan nyaman untuk hari-hari santai.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[16], 'name' => 'Kemeja UBSI Oxford Button Down - Putih',                 'price' => 155000, 'original_price' => 190000, 'rating' => 4.6, 'reviews_count' => 167, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'White',      'hex' => '#FFFFFF']], 'stock' => 42,  'weight' => 350, 'is_recommended' => true,  'description' => 'Kemeja oxford putih formal UBSI, pilihan sempurna untuk sidang skripsi dan acara wisuda.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[17], 'name' => 'Kemeja UBSI Oxford Button Down - Biru Muda',             'price' => 155000, 'original_price' => null,   'rating' => 4.4, 'reviews_count' => 112, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Light Blue', 'hex' => '#90CAF9']], 'stock' => 38,  'weight' => 350, 'is_recommended' => false, 'description' => 'Kemeja oxford biru muda, kesan fresh profesional untuk kegiatan akademik dan seminar.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[18], 'name' => 'Jaket UBSI Bomber - Olive Green',                        'price' => 225000, 'original_price' => 275000, 'rating' => 4.7, 'reviews_count' => 230, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Olive',      'hex' => '#808000']], 'stock' => 25,  'weight' => 600, 'is_recommended' => true,  'description' => 'Jaket bomber olive green UBSI dengan ribbing kuning di collar, sleeve, dan hem. Gaya street style terkini.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[19], 'name' => 'Jaket UBSI Windbreaker - Biru-Kuning',                   'price' => 195000, 'original_price' => null,   'rating' => 4.5, 'reviews_count' => 175, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Blue-Yellow','hex' => '#0D47A1']], 'stock' => 33,  'weight' => 400, 'is_recommended' => false, 'description' => 'Windbreaker ringan biru-kuning UBSI, tahan angin untuk kegiatan outdoor dan olahraga kampus.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[20], 'name' => 'Kaos UBSI Graphic Tee - Putih Hitam',                   'price' => 75000,  'original_price' => 90000,  'rating' => 4.1, 'reviews_count' => 78,  'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'White',      'hex' => '#FFFFFF']], 'stock' => 130, 'weight' => 240, 'is_recommended' => false, 'description' => 'Kaos graphic tee desain artwork eksklusif UBSI hitam putih, pilihan kasual yang statement.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[21], 'name' => 'Kaos UBSI Tie Dye - Multi Color',                        'price' => 89000,  'original_price' => 105000, 'rating' => 4.3, 'reviews_count' => 102, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Multicolor', 'hex' => '#FF6B6B']], 'stock' => 55,  'weight' => 250, 'is_recommended' => false, 'description' => 'Kaos tie dye warna-warni unik khas UBSI, setiap kaos memiliki pola yang berbeda dan eksklusif.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[22], 'name' => 'Hoodie UBSI Zipper - Maroon',                           'price' => 168000, 'original_price' => 195000, 'rating' => 4.6, 'reviews_count' => 225, 'sizes' => ['S','M','L','XL','XXL'], 'colors' => [['name' => 'Maroon',     'hex' => '#800000']], 'stock' => 38,  'weight' => 520, 'is_recommended' => true,  'description' => 'Hoodie zipper maroon UBSI dengan dua kantong besar, hangat dan gampang dipakai-lepas.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[23], 'name' => 'Hoodie UBSI Zipper - Abu-abu',                          'price' => 168000, 'original_price' => null,   'rating' => 4.5, 'reviews_count' => 187, 'sizes' => ['S','M','L','XL','XXL'], 'colors' => [['name' => 'Grey',       'hex' => '#9E9E9E']], 'stock' => 42,  'weight' => 520, 'is_recommended' => false, 'description' => 'Hoodie zipper abu UBSI, netral cocok untuk semua warna bawahan, bahan fleece anti pilling.'],
            ['category_id' => $baju->id, 'main_photo' => $bajuPhotos[24], 'name' => 'Jersey UBSI Sport Dry Fit - Biru-Putih',                'price' => 120000, 'original_price' => 145000, 'rating' => 4.7, 'reviews_count' => 265, 'sizes' => ['S','M','L','XL'],       'colors' => [['name' => 'Blue-White', 'hex' => '#1976D2']], 'stock' => 65,  'weight' => 220, 'is_recommended' => true,  'description' => 'Jersey olahraga dry fit UBSI, menyerap keringat dengan cepat. Wajib untuk turnamen antar kampus!'],
 
            // =========================================================
            // TUMBLER (18 produk)
            // =========================================================
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[0],  'name' => 'Tumbler UBSI Plastik BPA Free - Hitam',              'price' => 75000,  'original_price' => 95000,  'rating' => 4.2, 'reviews_count' => 88,  'sizes' => ['500ml'], 'colors' => [['name' => 'Black',     'hex' => '#050505']], 'stock' => 85,  'weight' => 350, 'is_recommended' => false, 'description' => 'Tumbler plastik BPA-free 500ml berlogo UBSI, ringan praktis untuk aktivitas kampus sehari-hari.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[1],  'name' => 'Tumbler UBSI Stainless Steel Double Wall - Silver',  'price' => 135000, 'original_price' => 165000, 'rating' => 4.8, 'reviews_count' => 340, 'sizes' => ['600ml'], 'colors' => [['name' => 'Silver',    'hex' => '#C0C0C0']], 'stock' => 60,  'weight' => 450, 'is_recommended' => true,  'description' => 'Tumbler stainless double wall 600ml, menjaga minuman panas 12 jam dan dingin 24 jam. Best seller!'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[2],  'name' => 'Tumbler UBSI Stainless Steel Double Wall - Hitam',  'price' => 135000, 'original_price' => null,   'rating' => 4.9, 'reviews_count' => 425, 'sizes' => ['600ml'], 'colors' => [['name' => 'Black',     'hex' => '#000000']], 'stock' => 45,  'weight' => 450, 'is_recommended' => true,  'description' => 'Tumbler stainless hitam double wall terlaris, desain minimalis premium dengan engraving UBSI.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[3],  'name' => 'Tumbler UBSI Plastik Warna - Merah',                'price' => 65000,  'original_price' => null,   'rating' => 4.0, 'reviews_count' => 55,  'sizes' => ['500ml'], 'colors' => [['name' => 'Red',       'hex' => '#D32F2F']], 'stock' => 100, 'weight' => 320, 'is_recommended' => false, 'description' => 'Tumbler plastik warna merah cerah berlogo UBSI, tutup anti tumpah dengan seal karet.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[4],  'name' => 'Tumbler UBSI Plastik Warna - Biru',                 'price' => 65000,  'original_price' => 80000,  'rating' => 4.1, 'reviews_count' => 67,  'sizes' => ['500ml'], 'colors' => [['name' => 'Blue',      'hex' => '#1976D2']], 'stock' => 90,  'weight' => 320, 'is_recommended' => false, 'description' => 'Tumbler plastik biru berlogo UBSI, desain ergonomis mudah digenggam saat perjalanan.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[5],  'name' => 'Tumbler UBSI Kaca Borosilicate - Clear',            'price' => 98000,  'original_price' => 120000, 'rating' => 4.5, 'reviews_count' => 143, 'sizes' => ['450ml'], 'colors' => [['name' => 'Clear',     'hex' => '#E8F5E9']], 'stock' => 30,  'weight' => 380, 'is_recommended' => true,  'description' => 'Tumbler kaca borosilicate bening 450ml dengan sleeve silikon logo UBSI, tampak elegan untuk kopi pagi.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[6],  'name' => 'Tumbler UBSI Kaca Borosilicate - Frosted',          'price' => 105000, 'original_price' => null,   'rating' => 4.6, 'reviews_count' => 162, 'sizes' => ['450ml'], 'colors' => [['name' => 'Frosted',   'hex' => '#ECEFF1']], 'stock' => 25,  'weight' => 380, 'is_recommended' => true,  'description' => 'Tumbler kaca frosted premium, cantik dan elegan dengan etching logo UBSI di badan kaca.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[7],  'name' => 'Tumbler UBSI Vacuum Flask Wide Mouth - Hijau',      'price' => 155000, 'original_price' => 185000, 'rating' => 4.7, 'reviews_count' => 234, 'sizes' => ['750ml'], 'colors' => [['name' => 'Green',     'hex' => '#2E7D32']], 'stock' => 22,  'weight' => 520, 'is_recommended' => true,  'description' => 'Vacuum flask wide mouth 750ml hijau UBSI, kapasitas besar untuk hiking dan camping.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[8],  'name' => 'Tumbler UBSI Vacuum Flask Wide Mouth - Orange',     'price' => 155000, 'original_price' => null,   'rating' => 4.5, 'reviews_count' => 188, 'sizes' => ['750ml'], 'colors' => [['name' => 'Orange',    'hex' => '#E65100']], 'stock' => 18,  'weight' => 520, 'is_recommended' => false, 'description' => 'Vacuum flask orange 750ml UBSI, warna cerah mudah ditemukan dalam tas, tahan banting.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[9],  'name' => 'Tumbler UBSI Infuser Bottle - Biru Muda',           'price' => 88000,  'original_price' => 105000, 'rating' => 4.3, 'reviews_count' => 119, 'sizes' => ['600ml'], 'colors' => [['name' => 'Light Blue','hex' => '#90CAF9']], 'stock' => 40,  'weight' => 360, 'is_recommended' => false, 'description' => 'Infuser bottle 600ml UBSI dengan saringan buah di tengah, minum sehat rasa buah segar setiap hari.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[10], 'name' => 'Tumbler UBSI Infuser Bottle - Pink',                'price' => 88000,  'original_price' => null,   'rating' => 4.4, 'reviews_count' => 98,  'sizes' => ['600ml'], 'colors' => [['name' => 'Pink',      'hex' => '#F48FB1']], 'stock' => 50,  'weight' => 360, 'is_recommended' => false, 'description' => 'Infuser bottle pink 600ml UBSI, pilihan cantik mahasiswi untuk hidup sehat dengan detox water.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[11], 'name' => 'Tumbler UBSI Smart Temp Display - Hitam',           'price' => 185000, 'original_price' => 220000, 'rating' => 4.8, 'reviews_count' => 298, 'sizes' => ['500ml'], 'colors' => [['name' => 'Black',     'hex' => '#000000']], 'stock' => 12,  'weight' => 480, 'is_recommended' => true,  'description' => 'Tumbler premium dengan display suhu digital di tutup, stainless body, logo UBSI laser engraving.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[12], 'name' => 'Tumbler UBSI Smart Temp Display - Silver',          'price' => 185000, 'original_price' => null,   'rating' => 4.7, 'reviews_count' => 221, 'sizes' => ['500ml'], 'colors' => [['name' => 'Silver',    'hex' => '#C0C0C0']], 'stock' => 10,  'weight' => 480, 'is_recommended' => true,  'description' => 'Tumbler smart display silver premium UBSI, hadiah sempurna untuk wisudawan dan mahasiswa berprestasi.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[13], 'name' => 'Tumbler UBSI Sport Squeeze Bottle - Hijau',         'price' => 55000,  'original_price' => 70000,  'rating' => 3.9, 'reviews_count' => 44,  'sizes' => ['700ml'], 'colors' => [['name' => 'Green',     'hex' => '#4CAF50']], 'stock' => 110, 'weight' => 250, 'is_recommended' => false, 'description' => 'Squeeze bottle 700ml UBSI, sempurna untuk olahraga dan gym, mudah diminum sambil bergerak.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[14], 'name' => 'Tumbler UBSI Sport Squeeze Bottle - Merah',         'price' => 55000,  'original_price' => null,   'rating' => 4.0, 'reviews_count' => 52,  'sizes' => ['700ml'], 'colors' => [['name' => 'Red',       'hex' => '#F44336']], 'stock' => 95,  'weight' => 250, 'is_recommended' => false, 'description' => 'Squeeze bottle merah 700ml UBSI, cap flip-top mudah dibuka tutup satu tangan.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[15], 'name' => 'Tumbler UBSI Ceramic Coated - Matcha Green',        'price' => 145000, 'original_price' => 175000, 'rating' => 4.6, 'reviews_count' => 178, 'sizes' => ['500ml'], 'colors' => [['name' => 'Matcha',    'hex' => '#7CB342']], 'stock' => 20,  'weight' => 440, 'is_recommended' => true,  'description' => 'Tumbler ceramic coated matcha green UBSI, lapisan dalam keramik anti rasa & bau, premium.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[16], 'name' => 'Tumbler UBSI Ceramic Coated - Dusty Rose',          'price' => 145000, 'original_price' => null,   'rating' => 4.5, 'reviews_count' => 143, 'sizes' => ['500ml'], 'colors' => [['name' => 'Dusty Rose','hex' => '#FFAB91']], 'stock' => 17,  'weight' => 440, 'is_recommended' => false, 'description' => 'Tumbler ceramic coated dusty rose estetik, cocok untuk konten OOTD dan coffee date.'],
            ['category_id' => $tumbler->id, 'main_photo' => $tumblerPhotos[17], 'name' => 'Tumbler UBSI Collapsible Silicone - Putih',         'price' => 78000,  'original_price' => 95000,  'rating' => 4.3, 'reviews_count' => 88,  'sizes' => ['350ml'], 'colors' => [['name' => 'White',     'hex' => '#FFFFFF']], 'stock' => 60,  'weight' => 180, 'is_recommended' => false, 'description' => 'Tumbler silikon lipat 350ml UBSI, bisa dilipat kecil masuk saku, sangat travel-friendly!'],
        ];
 
        $inserted = 0;
        $updated  = 0;
        foreach ($products as $pData) {
            $slug    = Str::slug($pData['name']);
            $product = Product::where('slug', $slug)->first();

            if (!$product) {
                // Insert baru
                Product::create([
                    'category_id'    => $pData['category_id'],
                    'slug'           => $slug,
                    'name'           => $pData['name'],
                    'sku'            => 'UBSI-' . strtoupper(Str::random(6)),
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
                ]);
                $inserted++;
            } else {
                // Selalu update foto jika seeder dijalankan ulang
                if (!empty($pData['main_photo'])) {
                    $product->update(['main_photo' => $pData['main_photo']]);
                    $updated++;
                }
            }
        }
 
        $this->command->info('✅ StoreSeeder selesai:');
        $this->command->info('   📦 Products: ' . $inserted . ' baru, ' . $updated . ' diupdate foto (total: ' . count($products) . ')');
        $this->command->info('   🏷️  Categories: ' . count($categoryNames));
        $this->command->info('   🚚 Expeditions: 5');
        $this->command->info('   🖼️  Foto dummy: di-download ke storage/app/public/products/');
    }
}
