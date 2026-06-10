<?php

namespace Database\Seeders;

use App\Models\Cart;
use App\Models\CustomerAddress;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // ============================
        // USERS
        // ============================

         // Superadmin
        $superadmin = User::updateOrCreate(
            ['email' => 'superadmin@ubsistore.test'],
            [
                'name'     => 'Superadmin ubsiStore',
                'password' => Hash::make('password123'),
                'role'     => User::ROLE_SUPERADMIN,
                'phone'    => '081111111111',
                'is_active'=> true,
            ]
        );

        // Admin
        $admin = User::updateOrCreate(
            ['email' => 'admin@ubsistore.test'],
            [
                'name'     => 'Admin ubsiStore',
                'password' => Hash::make('password123'),
                'role'     => User::ROLE_ADMIN,
                'phone'    => '082222222222',
                'is_active'=> true,
            ]
        );

        // Admin kedua
        User::updateOrCreate(
            ['email' => 'admin2@ubsistore.test'],
            [
                'name'     => 'Admin Dua ubsiStore',
                'password' => Hash::make('password123'),
                'role'     => User::ROLE_ADMIN,
                'phone'    => '082233334444',
                'is_active'=> true,
            ]
        );

        // Customers
        $customers = [
            [
                'name'    => 'Budi Santoso',
                'email'   => 'customer@ubsistore.test',
                
                'phone'   => '083333333333',
                'address' => [
                    'label'          => 'Rumah',
                    'receiver_name'  => 'Budi Santoso',
                    'address'        => 'Jl. Margonda Raya No. 10',
                    'province'       => 'Jawa Barat',
                    'city'           => 'Depok',
                    'district'       => 'Beji',
                    'village'        => 'Pondok Cina',
                    'postal_code'    => '16424',
                    'latitude'       => -6.3686000,
                    'longitude'      => 106.8317000,
                    'is_default'     => true,
                ],
            ],
            [
                'name'    => 'Siti Rahayu',
                'email'   => 'siti@ubsistore.test',
               
                'phone'   => '084444444444',
                'address' => [
                    'label'          => 'Kos',
                    'receiver_name'  => 'Siti Rahayu',
                    'address'        => 'Jl. Raya Bogor No. 25',
                    'province'       => 'DKI Jakarta',
                    'city'           => 'Jakarta Timur',
                    'district'       => 'Ciracas',
                    'village'        => 'Ciracas',
                    'postal_code'    => '13740',
                    'latitude'       => -6.3514000,
                    'longitude'      => 106.8783000,
                    'is_default'     => true,
                ],
            ],
            [
                'name'    => 'Ahmad Fauzan',
                'email'   => 'ahmad@ubsistore.test',
               
                'phone'   => '085555555555',
                'address' => [
                    'label'          => 'Kampus',
                    'receiver_name'  => 'Ahmad Fauzan',
                    'address'        => 'Jl. Damai No. 8, Kelurahan Warung Jati Barat',
                    'province'       => 'DKI Jakarta',
                    'city'           => 'Jakarta Selatan',
                    'district'       => 'Pasar Minggu',
                    'village'        => 'Warung Jati Barat',
                    'postal_code'    => '12540',
                    'latitude'       => -6.2910000,
                    'longitude'      => 106.8440000,
                    'is_default'     => true,
                ],
            ],
            [
                'name'    => 'Dewi Permata',
                'email'   => 'dewi@ubsistore.test',
               
                'phone'   => '086666666666',
                'address' => [
                    'label'          => 'Rumah',
                    'receiver_name'  => 'Dewi Permata',
                    'address'        => 'Jl. Raya Serpong No. 15',
                    'province'       => 'Banten',
                    'city'           => 'Tangerang Selatan',
                    'district'       => 'Serpong',
                    'village'        => 'Rawa Buntu',
                    'postal_code'    => '15310',
                    'latitude'       => -6.3293000,
                    'longitude'      => 106.6540000,
                    'is_default'     => true,
                ],
            ],
            [
                'name'    => 'Rizky Pratama',
                'email'   => 'rizky@ubsistore.test',
                
                'phone'   => '087777777777',
                'address' => [
                    'label'          => 'Apartemen',
                    'receiver_name'  => 'Rizky Pratama',
                    'address'        => 'Jl. Kamal Raya No. 5, Blok A-12',
                    'province'       => 'DKI Jakarta',
                    'city'           => 'Jakarta Barat',
                    'district'       => 'Kalideres',
                    'village'        => 'Kamal',
                    'postal_code'    => '11810',
                    'latitude'       => -6.1342000,
                    'longitude'      => 106.6960000,
                    'is_default'     => true,
                ],
            ],
        ];

        foreach ($customers as $cData) {
            $customer = User::updateOrCreate(
                ['email' => $cData['email']],
                [
                    'name'      => $cData['name'],
                    'password'  => Hash::make('password123'),
                    'role'      => User::ROLE_CUSTOMER,
                    
                    'phone'     => $cData['phone'],
                    'is_active' => true,
                ]
            );

            Cart::firstOrCreate(['user_id' => $customer->id]);

            CustomerAddress::updateOrCreate(
                ['user_id' => $customer->id, 'label' => $cData['address']['label']],
                array_merge($cData['address'], ['phone' => $cData['phone']])
            );
        }
    }
}
