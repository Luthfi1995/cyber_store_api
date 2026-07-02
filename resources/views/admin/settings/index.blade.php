@extends('admin.layouts.app')

@section('title', 'Pengaturan Toko')
@section('page-title', 'Pengaturan Toko')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <span>Pengaturan Toko</span>
@endsection

@section('content')
    <div style="max-width: 700px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">⚙️ Konfigurasi Informasi Toko</span>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- Nama Toko --}}
                    <div class="form-group">
                        <label class="form-label" for="store_name">Nama Toko <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="store_name" name="store_name" class="form-control" 
                            value="{{ old('store_name', $storeName) }}" placeholder="Masukkan nama toko" required>
                        @error('store_name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Logo Toko --}}
                    <div class="form-group">
                        <label class="form-label" for="store_logo">Logo Toko</label>
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 12px;">
                            @php
                                $logoSetting = \App\Models\Setting::get('store_logo');
                                $logoUrl = $logoSetting ? Storage::disk('public')->url($logoSetting) : asset('/assets/img/logo.png');
                            @endphp
                            <img src="{{ $logoUrl }}" alt="Logo Toko" style="max-height: 80px; max-width: 150px; object-fit: contain; border-radius: 4px; border: 1px solid var(--border-color, #ccc); padding: 4px; background: white;">
                        </div>
                        <input type="file" id="store_logo" name="store_logo" class="form-control" accept="image/*">
                        <div class="form-hint">Format yang diperbolehkan: PNG, JPG, JPEG, WEBP. Maksimal 2MB.</div>
                        @error('store_logo')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Kota Asal Pengiriman (RajaOngkir) --}}
                    <div class="form-group">
                        <label class="form-label" for="store_city_id">Kota Asal Pengiriman (RajaOngkir) <span style="color:var(--danger)">*</span></label>
                        <select id="store_city_id" name="store_city_id" class="form-control" required style="max-height: 200px;">
                            <option value="">— Pilih Kota —</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city['city_id'] }}" {{ old('store_city_id', $storeCityId) == $city['city_id'] ? 'selected' : '' }}>
                                    {{ $city['type'] }} {{ $city['city_name'] }} ({{ $city['province'] }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-hint">Digunakan sebagai titik asal (origin) perhitungan ongkos kirim API RajaOngkir.</div>
                        @error('store_city_id')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email Toko --}}
                    <div class="form-group">
                        <label class="form-label" for="store_email">Email Toko <span style="color:var(--danger)">*</span></label>
                        <input type="email" id="store_email" name="store_email" class="form-control" 
                            value="{{ old('store_email', $storeEmail) }}" placeholder="Masukkan email toko" required>
                        @error('store_email')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Telepon Toko --}}
                    <div class="form-group">
                        <label class="form-label" for="store_phone">No. Telepon Toko <span style="color:var(--danger)">*</span></label>
                        <input type="text" id="store_phone" name="store_phone" class="form-control" 
                            value="{{ old('store_phone', $storePhone) }}" placeholder="Masukkan nomor telepon toko" required>
                        @error('store_phone')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Alamat Toko --}}
                    <div class="form-group">
                        <label class="form-label" for="store_address">Alamat Lengkap Toko <span style="color:var(--danger)">*</span></label>
                        <textarea id="store_address" name="store_address" class="form-control" rows="4" 
                            placeholder="Tulis alamat lengkap toko..." required>{{ old('store_address', $storeAddress) }}</textarea>
                        @error('store_address')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div style="display:flex; justify-content:flex-end; margin-top:24px;">
                        <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
