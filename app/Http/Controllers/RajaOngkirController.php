<?php

namespace App\Http\Controllers\RajaOngkirController;

use App\Http\Controllers\Controller;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class RajaOngkirController extends Controller
{
    public function getProvinces()
    {
        try {
            $response = Http::withoutVerifying()->timeout(5)->withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->get(env('RAJAONGKIR_BASE_URL') . '/province');

            if ($response->failed()) {
                throw new \Exception('API request failed');
            }
            return response()->json($response->json());
        } catch (\Exception $e) {
            $path = database_path('data/rajaongkir_provinces.json');
            if (File::exists($path)) {
                $provinces = json_decode(File::get($path), true);
                return response()->json([
                    'rajaongkir' => [
                        'status' => ['code' => 200, 'description' => 'OK (Offline Fallback)'],
                        'results' => $provinces
                    ]
                ]);
            }

            return response()->json([
                'rajaongkir' => [
                    'status' => ['code' => 200, 'description' => 'OK (Mocked)'],
                    'results' => [
                        ['province_id' => '6', 'province' => 'DKI Jakarta'],
                        ['province_id' => '9', 'province' => 'Jawa Barat'],
                        ['province_id' => '10', 'province' => 'Jawa Tengah'],
                        ['province_id' => '11', 'province' => 'Jawa Timur'],
                        ['province_id' => '5', 'province' => 'DI Yogyakarta'],
                    ]
                ]
            ]);
        }
    }

    public function getCities(Request $request)
    {
        $provinceId = $request->input('province_id');
        try {
            $response = Http::withoutVerifying()->timeout(5)->withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->get(env('RAJAONGKIR_BASE_URL') . '/city', [
                'province' => $provinceId
            ]);

            if ($response->failed()) {
                throw new \Exception('API request failed');
            }
            return response()->json($response->json());
        } catch (\Exception $e) {
            $path = database_path('data/rajaongkir_cities.json');
            if (File::exists($path)) {
                $cities = json_decode(File::get($path), true);
                $filtered = array_values(array_filter($cities, function ($city) use ($provinceId) {
                    return $city['province_id'] == $provinceId;
                }));
                return response()->json([
                    'rajaongkir' => [
                        'status' => ['code' => 200, 'description' => 'OK (Offline Fallback)'],
                        'results' => $filtered
                    ]
                ]);
            }

            $mockCities = [
                '6' => [ // DKI Jakarta
                    ['city_id' => '151', 'province_id' => '6', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Barat', 'postal_code' => '11610'],
                    ['city_id' => '152', 'province_id' => '6', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Pusat', 'postal_code' => '10110'],
                    ['city_id' => '153', 'province_id' => '6', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Selatan', 'postal_code' => '12110'],
                    ['city_id' => '154', 'province_id' => '6', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Timur', 'postal_code' => '13110'],
                    ['city_id' => '155', 'province_id' => '6', 'province' => 'DKI Jakarta', 'type' => 'Kota', 'city_name' => 'Jakarta Utara', 'postal_code' => '14110'],
                ],
                '9' => [ // Jawa Barat
                    ['city_id' => '23', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Bandung', 'postal_code' => '40111'],
                    ['city_id' => '54', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Bekasi', 'postal_code' => '17111'],
                    ['city_id' => '78', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Bogor', 'postal_code' => '16111'],
                    ['city_id' => '115', 'province_id' => '9', 'province' => 'Jawa Barat', 'type' => 'Kota', 'city_name' => 'Depok', 'postal_code' => '16411'],
                ],
                '5' => [ // DI Yogyakarta
                    ['city_id' => '39', 'province_id' => '5', 'province' => 'DI Yogyakarta', 'type' => 'Kabupaten', 'city_name' => 'Bantul', 'postal_code' => '55715'],
                    ['city_id' => '135', 'province_id' => '5', 'province' => 'DI Yogyakarta', 'type' => 'Kabupaten', 'city_name' => 'Sleman', 'postal_code' => '55511'],
                    ['city_id' => '501', 'province_id' => '5', 'province' => 'DI Yogyakarta', 'type' => 'Kota', 'city_name' => 'Yogyakarta', 'postal_code' => '55111'],
                ]
            ];

            $results = $mockCities[$provinceId] ?? [
                ['city_id' => '152', 'province_id' => $provinceId, 'province' => 'Mock Province', 'type' => 'Kota', 'city_name' => 'Mock City', 'postal_code' => '10000']
            ];

            return response()->json([
                'rajaongkir' => [
                    'status' => ['code' => 200, 'description' => 'OK (Mocked)'],
                    'results' => $results
                ]
            ]);
        }
    }

    public function getCost(Request $request)
    {
        $origin = $request->input('origin');
        $destination = $request->input('destination');
        $weight = $request->input('weight');
        $courier = $request->input('courier');

        try {
            $response = Http::withoutVerifying()->timeout(5)->withHeaders([
                'key' => env('RAJAONGKIR_API_KEY')
            ])->post(env('RAJAONGKIR_BASE_URL') . '/cost', [
                'origin' => $origin,
                'destination' => $destination,
                'weight' => $weight,
                'courier' => $courier,
            ]);

            if ($response->failed()) {
                throw new \Exception('API request failed');
            }
            return response()->json($response->json());
        } catch (\Exception $e) {
            $costValue = $courier === 'jne' ? 15000 : ($courier === 'pos' ? 12000 : 18000);
            return response()->json([
                'rajaongkir' => [
                    'status' => ['code' => 200, 'description' => 'OK (Mocked)'],
                    'results' => [
                        [
                            'code' => $courier ?? 'jne',
                            'name' => strtoupper($courier ?? 'jne'),
                            'costs' => [
                                [
                                    'service' => 'REG',
                                    'description' => 'Layanan Reguler (Mock)',
                                    'cost' => [
                                        [
                                            'value' => $costValue,
                                            'etd' => '2-3',
                                            'note' => ''
                                        ]
                                    ]
                                ],
                                [
                                    'service' => 'OKE',
                                    'description' => 'Layanan Ekonomis (Mock)',
                                    'cost' => [
                                        [
                                            'value' => $costValue - 3000,
                                            'etd' => '3-5',
                                            'note' => ''
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]);
        }
    }
}