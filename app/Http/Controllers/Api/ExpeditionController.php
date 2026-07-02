<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use App\Models\CustomerAddress;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;

class ExpeditionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $address = null;
        $addressId = $request->input('address_id');
        
        if ($addressId) {
            $address = CustomerAddress::find($addressId);
        } elseif ($request->user()) {
            $address = $request->user()->addresses()->where('is_default', true)->first();
        }

        $destinationCityId = null;

        if ($address && $address->city) {
            $userCityName = strtolower(trim($address->city));
            $userCityName = str_replace(['kota ', 'kabupaten '], '', $userCityName);

            $path = database_path('data/rajaongkir_cities.json');
            if (File::exists($path)) {
                $cities = json_decode(File::get($path), true);
                foreach ($cities as $c) {
                    $dbCityName = strtolower($c['city_name']);
                    if (str_contains($dbCityName, $userCityName) || str_contains($userCityName, $dbCityName)) {
                        $destinationCityId = $c['city_id'];
                        break;
                    }
                }
            }
        }

        $rajaOngkirCosts = [];
        if ($destinationCityId) {
            $quantity = max(1, (int) $request->integer('quantity', 1));
            $weight = $quantity * 1000;
            $originCityId = (int) \App\Models\Setting::get('store_city_id', 152);
            
            $cacheKey = "rajaongkir_costs_{$originCityId}_{$destinationCityId}_{$weight}";
            
            $rajaOngkirCosts = cache()->remember($cacheKey, now()->addHours(1), function () use ($originCityId, $destinationCityId, $weight, $quantity) {
                $costsData = [];
                // 1. Query JNE cost
                try {
                    $response = Http::withoutVerifying()->timeout(3)->withHeaders([
                        'key' => env('RAJAONGKIR_API_KEY')
                    ])->post(env('RAJAONGKIR_BASE_URL') . '/cost', [
                        'origin' => $originCityId,
                        'destination' => $destinationCityId,
                        'weight' => $weight,
                        'courier' => 'jne',
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $costs = $data['rajaongkir']['results'][0]['costs'] ?? [];
                        foreach ($costs as $c) {
                            if ($c['service'] == 'REG') {
                                $costsData['jne_reg'] = [
                                    'cost' => $c['cost'][0]['value'] ?? null,
                                    'etd' => $c['cost'][0]['etd'] ?? null,
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore and try next
                }

                // 2. Query POS cost
                try {
                    $response = Http::withoutVerifying()->timeout(3)->withHeaders([
                        'key' => env('RAJAONGKIR_API_KEY')
                    ])->post(env('RAJAONGKIR_BASE_URL') . '/cost', [
                        'origin' => $originCityId,
                        'destination' => $destinationCityId,
                        'weight' => $weight,
                        'courier' => 'pos',
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $costs = $data['rajaongkir']['results'][0]['costs'] ?? [];
                        foreach ($costs as $c) {
                            if ($c['service'] == 'Pos Kilat Khusus' || $c['service'] == 'KILAT') {
                                $costsData['pos'] = [
                                    'cost' => $c['cost'][0]['value'] ?? null,
                                    'etd' => $c['cost'][0]['etd'] ?? null,
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore and try next
                }

                // 3. Query TIKI cost
                try {
                    $response = Http::withoutVerifying()->timeout(3)->withHeaders([
                        'key' => env('RAJAONGKIR_API_KEY')
                    ])->post(env('RAJAONGKIR_BASE_URL') . '/cost', [
                        'origin' => $originCityId,
                        'destination' => $destinationCityId,
                        'weight' => $weight,
                        'courier' => 'tiki',
                    ]);

                    if ($response->successful()) {
                        $data = $response->json();
                        $costs = $data['rajaongkir']['results'][0]['costs'] ?? [];
                        foreach ($costs as $c) {
                            if ($c['service'] == 'REG') {
                                $costsData['tiki'] = [
                                    'cost' => $c['cost'][0]['value'] ?? null,
                                    'etd' => $c['cost'][0]['etd'] ?? null,
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore
                }

                // Fallback default jika semua API request gagal (offline / limit)
                if (empty($costsData)) {
                    $costValue = 15000 + (($quantity - 1) * 5000);
                    $costsData['jne_reg'] = ['cost' => $costValue, 'etd' => '2-3'];
                    $costsData['pos'] = ['cost' => max(9000, $costValue - 3000), 'etd' => '3-5'];
                    $costsData['tiki'] = ['cost' => max(10000, $costValue - 1000), 'etd' => '2-4'];
                }

                return $costsData;
            });
        }

        $expeditions = Expedition::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Expedition $expedition) use ($request, $rajaOngkirCosts) {
                $quantity = max(1, (int) $request->integer('quantity', 1));

                $baseCost = (float) $expedition->base_cost;
                $estimatedDays = $expedition->estimated_days;

                if (!empty($rajaOngkirCosts)) {
                    // Cek tarif berdasarkan kode kurir
                    if ($expedition->code === 'jne_reg' && isset($rajaOngkirCosts['jne_reg'])) {
                        $baseCost = $rajaOngkirCosts['jne_reg']['cost'];
                        $etd = $rajaOngkirCosts['jne_reg']['etd'];
                        if ($etd) {
                            $days = (int) filter_var($etd, FILTER_SANITIZE_NUMBER_INT);
                            if ($days > 0) $estimatedDays = $days;
                        }
                    } elseif ($expedition->code === 'pos' && isset($rajaOngkirCosts['pos'])) {
                        $baseCost = $rajaOngkirCosts['pos']['cost'];
                        $etd = $rajaOngkirCosts['pos']['etd'];
                        if ($etd) {
                            $days = (int) filter_var($etd, FILTER_SANITIZE_NUMBER_INT);
                            if ($days > 0) $estimatedDays = $days;
                        }
                    } elseif ($expedition->code === 'tiki' && isset($rajaOngkirCosts['tiki'])) {
                        $baseCost = $rajaOngkirCosts['tiki']['cost'];
                        $etd = $rajaOngkirCosts['tiki']['etd'];
                        if ($etd) {
                            $days = (int) filter_var($etd, FILTER_SANITIZE_NUMBER_INT);
                            if ($days > 0) $estimatedDays = $days;
                        }
                    } elseif ($expedition->code === 'sicepat') {
                        // SiCepat tidak ada di RajaOngkir starter, estimasikan dari JNE
                        $jneCost = $rajaOngkirCosts['jne_reg']['cost'] ?? 15000;
                        $baseCost = max(8000, $jneCost - 2000);
                    }
                } else {
                    $extraWeightFee = max(0, $quantity - 1) * 1000;
                    $baseCost = $baseCost + $extraWeightFee;
                }

                return [
                    'id' => $expedition->id,
                    'name' => $expedition->name,
                    'code' => $expedition->code,
                    'service' => $expedition->service,
                    'base_cost' => (float) $expedition->base_cost,
                    'shipping_cost' => (float) $baseCost,
                    'estimated_days' => $estimatedDays,
                ];
            });

        return response()->json([
            'expeditions' => $expeditions,
        ]);
    }
}
