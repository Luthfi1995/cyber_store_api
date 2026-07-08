@extends('admin.layouts.app')

@section('title', 'Tambah Produk')
@section('page-title', 'Tambah Produk')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <a href="{{ route('admin.products.index') }}">Produk</a>
    <span class="breadcrumb-sep">›</span>
    <span>Tambah</span>
@endsection

@section('content')
    <div style="max-width: 800px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">📦 Tambah Produk Baru</span>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">← Kembali</a>
            </div>
            <div class="card-body">
                <form id="productCreateForm" method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                    @csrf

                    {{-- Gambar Produk --}}
                    <div
                        style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border);">
                        🖼️ Gambar Produk
                    </div>
                    <div class="form-row" style="gap: 16px; margin-bottom: 20px;">
                        {{-- Gambar 1 (Utama) --}}
                        <div class="form-group" style="flex: 1; min-width: 220px;">
                            <label class="form-label" style="font-weight: 600;">Gambar 1 (Utama) <span style="color:var(--danger)">*</span></label>
                            <div class="avatar-upload-wrap">
                                <div class="avatar-preview" id="avatarPreviewWrap"
                                    style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                    <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">📦</div>
                                </div>
                                <div class="avatar-upload-actions">
                                    <label style="cursor:pointer;">
                                        <input type="file" name="main_photo" required
                                            accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                            onchange="previewPhoto(this, 'avatarPreviewWrap')">
                                        <span class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">📷 Pilih</span>
                                    </label>
                                    <div style="font-size:10px;color:var(--text-muted);">Maks 2MB</div>
                                </div>
                            </div>
                            @error('main_photo')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Gambar 2 --}}
                        <div class="form-group" style="flex: 1; min-width: 220px;">
                            <label class="form-label" style="font-weight: 600;">Gambar 2</label>
                            <div class="avatar-upload-wrap">
                                <div class="avatar-preview" id="previewWrap2"
                                    style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                    <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">📷</div>
                                </div>
                                <div class="avatar-upload-actions">
                                    <label style="cursor:pointer;">
                                        <input type="file" name="photo_2"
                                            accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                            onchange="previewPhoto(this, 'previewWrap2')">
                                        <span class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">📷 Pilih</span>
                                    </label>
                                    <div style="font-size:10px;color:var(--text-muted);">Maks 2MB</div>
                                </div>
                            </div>
                            @error('photo_2')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Gambar 3 --}}
                        <div class="form-group" style="flex: 1; min-width: 220px;">
                            <label class="form-label" style="font-weight: 600;">Gambar 3</label>
                            <div class="avatar-upload-wrap">
                                <div class="avatar-preview" id="previewWrap3"
                                    style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                    <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">📷</div>
                                </div>
                                <div class="avatar-upload-actions">
                                    <label style="cursor:pointer;">
                                        <input type="file" name="photo_3"
                                            accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                            onchange="previewPhoto(this, 'previewWrap3')">
                                        <span class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">📷 Pilih</span>
                                    </label>
                                    <div style="font-size:10px;color:var(--text-muted);">Maks 2MB</div>
                                </div>
                            </div>
                            @error('photo_3')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Info Dasar --}}
                    <div
                        style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); margin-bottom:14px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                        📋 Informasi Dasar
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="name">Nama Produk <span
                                style="color:var(--danger)">*</span></label>
                        <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}"
                            placeholder="Masukkan nama produk" required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="category_id">Kategori <span
                                    style="color:var(--danger)">*</span></label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                <option value="">— Pilih Kategori —</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="sku">SKU</label>
                            <input type="text" id="sku" name="sku" class="form-control"
                                value="{{ old('sku') }}" placeholder="Kosongkan untuk generate otomatis">
                            @error('sku')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Deskripsi Produk</label>
                        <textarea id="description" name="description" class="form-control" rows="4"
                            placeholder="Masukkan deskripsi produk...">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Harga & Stok --}}
                    <div
                        style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); margin:20px 0 14px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                        💰 Harga & Stok
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="price">Harga Jual (Rp) <span
                                    style="color:var(--danger)">*</span></label>
                            <input type="number" id="price" name="price" class="form-control"
                                value="{{ old('price') }}" placeholder="85000" min="0" step="500" required>
                            @error('price')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="original_price">Harga Asli / Coret (Rp)</label>
                            <input type="number" id="original_price" name="original_price" class="form-control"
                                value="{{ old('original_price') }}" placeholder="Opsional" min="0" step="500">
                            @error('original_price')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="stock">Stok <span
                                    style="color:var(--danger)">*</span></label>
                            <input type="number" id="stock" name="stock" class="form-control"
                                value="{{ old('stock', 0) }}" min="0" required>
                            @error('stock')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="weight">Berat (gram) <span
                                    style="color:var(--danger)">*</span></label>
                            <input type="number" id="weight" name="weight" class="form-control"
                                value="{{ old('weight', 200) }}" min="1" required>
                            @error('weight')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Varian --}}
                    <div
                        style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); margin:20px 0 14px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                        🎨 Varian Produk
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="sizes">Ukuran</label>
                            <input type="text" id="sizes" name="sizes" class="form-control"
                                value="{{ old('sizes') }}" placeholder="S, M, L, XL (pisahkan dengan koma)">
                            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Contoh: S, M, L, XL atau
                                All Size</div>
                            @error('sizes')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label">Warna & Palet</label>
                            <div id="color-list" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:8px;"></div>
                            <div style="display:flex; gap:8px; align-items:center;">
                                <input type="text" id="color-name" class="form-control"
                                    placeholder="Nama Warna (Cth: Merah)" style="flex:1;">
                                <input type="color" id="color-hex" class="form-control"
                                    style="width:50px; padding:2px; height:38px; cursor:pointer;" value="#ff0000">
                                <button type="button" class="btn btn-secondary" onclick="addColor()"
                                    style="height:38px; padding: 0 16px;">+ Tambah</button>
                            </div>
                            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Tambahkan nama warna dan
                                pilih palet hex agar tampil di aplikasi.</div>
                            @error('colors')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    {{-- Pengaturan --}}
                    <div
                        style="font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.8px; color:var(--text-muted); margin:20px 0 14px; padding-bottom:8px; border-bottom:1px solid var(--border);">
                        ⚙️ Pengaturan
                    </div>

                    <div style="display:flex; gap:24px; flex-wrap:wrap;">
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-check">
                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                    {{ old('is_active', '1') ? 'checked' : '' }}>
                                <span class="form-check-label">Produk Aktif (tampil di toko)</span>
                            </label>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-check">
                                <input type="checkbox" name="is_recommended" value="1" class="form-check-input"
                                    {{ old('is_recommended') ? 'checked' : '' }}>
                                <span class="form-check-label">Tandai sebagai Rekomendasi</span>
                            </label>
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:24px;">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="button" class="btn btn-primary" onclick="confirmCreate('productCreateForm', 'Konfirmasi Tambah Produk', 'Apakah Anda yakin ingin menyimpan produk baru ini?')">💾 Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

      <script>
        function previewPhoto(input, targetId) {
            if (input.files && input.files[0]) {
                const reader = new window.FileReader();
                reader.onload = function(e) {
                    const wrap = document.getElementById(targetId);
                    if (wrap) {
                        wrap.innerHTML =
                            `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius: var(--radius-sm);">`;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        let colorIndex = 0;

        document.addEventListener("DOMContentLoaded", function() {
            const oldColors = @json(old('colors', []));
            if (Array.isArray(oldColors)) {
                oldColors.forEach(c => {
                    if (typeof c === 'object' && c.name) {
                        addColorData(c.name, c.hex || '#000000');
                    } else if (typeof c === 'string') {
                        addColorData(c, '#000000');
                    }
                });
            } else if (typeof oldColors === 'string' && oldColors.trim() !== '') {
                oldColors.split(',').forEach(c => {
                    if (c.trim()) addColorData(c.trim(), '#000000');
                });
            }
        });

        function addColorData(name, hex) {
            const container = document.getElementById('color-list');
            const wrapper = document.createElement('div');
            wrapper.style.cssText =
                "display:flex; align-items:center; gap:6px; background:#f8f9fa; border:1px solid #dee2e6; padding:4px 8px; border-radius:4px;";
            wrapper.id = 'color-item-' + colorIndex;

            wrapper.innerHTML = `
        <div style="width:16px; height:16px; border-radius:50%; background-color:${hex}; border:1px solid #ccc;"></div>
        <span style="font-size:13px; font-weight:500;">${name}</span>
        <input type="hidden" name="colors[${colorIndex}][name]" value="${name}">
        <input type="hidden" name="colors[${colorIndex}][hex]" value="${hex}">
        <button type="button" style="background:none; border:none; color:red; cursor:pointer; font-weight:bold; margin-left:4px; font-size:16px; line-height:1;" onclick="removeColor(${colorIndex})">&times;</button>
    `;
            container.appendChild(wrapper);
            colorIndex++;
        }

        function addColor() {
            const nameInput = document.getElementById('color-name');
            const hexInput = document.getElementById('color-hex');
            const name = nameInput.value.trim();
            const hex = hexInput.value;

            if (!name) {
                alert('Silakan masukkan nama warna terlebih dahulu.');
                return;
            }

            addColorData(name, hex);
            nameInput.value = '';
        }

        function removeColor(index) {
            const el = document.getElementById('color-item-' + index);
            if (el) el.remove();
        }
    </script>
@endsection

