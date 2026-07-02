<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingController extends Controller
{
    public function index()
    {
        $storeName = Setting::get('store_name', 'UBSI Store');
        $storeAddress = Setting::get('store_address', 'Jl. Kramat Raya No.98, Senen, Jakarta Pusat');
        $storeCityId = Setting::get('store_city_id', '152');
        $storeEmail = Setting::get('store_email', 'support@bsi.ac.id');
        $storePhone = Setting::get('store_phone', '(021) 7867868');

        $cities = [];
        $citiesPath = database_path('data/rajaongkir_cities.json');
        if (File::exists($citiesPath)) {
            $cities = json_decode(File::get($citiesPath), true);
        }

        return view('admin.settings.index', compact('storeName', 'storeAddress', 'storeCityId', 'storeEmail', 'storePhone', 'cities'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'store_name' => ['required', 'string', 'max:200'],
            'store_address' => ['required', 'string'],
            'store_city_id' => ['required', 'integer'],
            'store_email' => ['required', 'email', 'max:200'],
            'store_phone' => ['required', 'string', 'max:50'],
            'store_logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:2048'],
        ]);

        Setting::set('store_name', $request->store_name);
        Setting::set('store_address', $request->store_address);
        Setting::set('store_city_id', $request->store_city_id);
        Setting::set('store_email', $request->store_email);
        Setting::set('store_phone', $request->store_phone);

        if ($request->hasFile('store_logo')) {
            $oldLogo = Setting::get('store_logo');
            if ($oldLogo) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldLogo);
            }
            $path = $request->file('store_logo')->store('settings', 'public');
            Setting::set('store_logo', $path);
        }

        // Fetch city name from JSON to store it
        $cityName = 'Jakarta Pusat';
        $citiesPath = database_path('data/rajaongkir_cities.json');
        if (File::exists($citiesPath)) {
            $cities = json_decode(File::get($citiesPath), true);
            foreach ($cities as $city) {
                if ($city['city_id'] == $request->store_city_id) {
                    $cityName = $city['type'] . ' ' . $city['city_name'];
                    break;
                }
            }
        }
        Setting::set('store_city_name', $cityName);

        return back()->with('success', 'Pengaturan toko berhasil diperbarui.');
    }
}
