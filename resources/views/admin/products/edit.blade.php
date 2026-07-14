@extends('admin.layouts.app')

@section('title', 'Edit Produk — ' . $product->name)
@section('page-title', 'Edit Produk')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <a href="{{ route('admin.products.index') }}">Produk</a>
    <span class="breadcrumb-sep">›</span>
    <span>Edit</span>
@endsection

@section('content')
    <div style="max-width: 800px;">
        <div class="card">
            <div class="card-header">
                <span class="card-title">✏️ Edit: {{ \Illuminate\Support\Str::limit($product->name, 40) }}</span>
                <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">← Kembali</a>
            </div>
            <div class="card-body">
                <form id="productEditForm" method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    {{-- Gambar Produk --}}
                    <div
                        style="font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:var(--text-muted);margin-bottom:14px;padding-bottom:8px;border-bottom:1px solid var(--border);">
                        🖼️ Gambar Produk
                    </div>
                    <div class="form-row" style="gap: 16px; margin-bottom: 20px;">
                        {{-- Gambar 1 (Utama) --}}
                        <div class="form-group" style="flex: 1; min-width: 220px;">
                            <label class="form-label" style="font-weight: 600;">Gambar 1 (Utama)</label>
                            <div class="avatar-upload-wrap">
                                <div class="avatar-preview" id="avatarPreviewWrap"
                                    style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                    @if ($product->main_photo)
                                        <img src="{{ Storage::disk('public')->url($product->main_photo) }}"
                                            alt="{{ $product->name }}"
                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                    @else
                                        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">📦</div>
                                    @endif
                                </div>
                                <div class="avatar-upload-actions">
                                    <label style="cursor:pointer;">
                                        <input type="file" name="main_photo" id="mainPhotoInput"
                                            accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                            onchange="previewPhoto(this, 'avatarPreviewWrap', 'removePhoto')">
                                        <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;">📷 Ganti</span>
                                    </label>
                                    @if ($product->main_photo)
                                        <label class="form-check" style="margin-top:4px;">
                                            <input type="checkbox" name="remove_main_photo" value="1"
                                                class="form-check-input" id="removePhoto" onchange="handleRemovePhoto(this, 'mainPhotoInput', 'avatarPreviewWrap', 'defaultPhotoHtml')">
                                            <span class="form-check-label" style="font-size:11px; color:var(--danger);">Hapus</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                            @error('main_photo')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Gambar 2 --}}
                        @php
                            $img2 = $product->images->where('sort_order', 1)->first();
                        @endphp
                        <div class="form-group" style="flex: 1; min-width: 220px;">
                            <label class="form-label" style="font-weight: 600;">Gambar 2</label>
                            <div class="avatar-upload-wrap">
                                <div class="avatar-preview" id="previewWrap2"
                                    style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                    @if ($img2)
                                        <img src="{{ Storage::disk('public')->url($img2->image) }}"
                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                    @else
                                        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">📷</div>
                                    @endif
                                </div>
                                <div class="avatar-upload-actions">
                                    <label style="cursor:pointer;">
                                        <input type="file" name="photo_2" id="photo2Input"
                                            accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                            onchange="previewPhoto(this, 'previewWrap2', 'removePhoto2')">
                                        <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;">📷 Ganti</span>
                                    </label>
                                    @if ($img2)
                                        <label class="form-check" style="margin-top:4px;">
                                            <input type="checkbox" name="remove_photo_2" value="1"
                                                class="form-check-input" id="removePhoto2" onchange="handleRemovePhoto(this, 'photo2Input', 'previewWrap2', 'emptyPhotoHtml')">
                                            <span class="form-check-label" style="font-size:11px; color:var(--danger);">Hapus</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                            @error('photo_2')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Gambar 3 --}}
                        @php
                            $img3 = $product->images->where('sort_order', 2)->first();
                        @endphp
                        <div class="form-group" style="flex: 1; min-width: 220px;">
                            <label class="form-label" style="font-weight: 600;">Gambar 3</label>
                            <div class="avatar-upload-wrap">
                                <div class="avatar-preview" id="previewWrap3"
                                    style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                    @if ($img3)
                                        <img src="{{ Storage::disk('public')->url($img3->image) }}"
                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                    @else
                                        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">📷</div>
                                    @endif
                                </div>
                                <div class="avatar-upload-actions">
                                    <label style="cursor:pointer;">
                                        <input type="file" name="photo_3" id="photo3Input"
                                            accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                            onchange="previewPhoto(this, 'previewWrap3', 'removePhoto3')">
                                        <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;">📷 Ganti</span>
                                    </label>
                                    @if ($img3)
                                        <label class="form-check" style="margin-top:4px;">
                                            <input type="checkbox" name="remove_photo_3" value="1"
                                                class="form-check-input" id="removePhoto3" onchange="handleRemovePhoto(this, 'photo3Input', 'previewWrap3', 'emptyPhotoHtml')">
                                            <span class="form-check-label" style="font-size:11px; color:var(--danger);">Hapus</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                            @error('photo_3')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row" style="gap: 16px; margin-bottom: 20px; flex-wrap: wrap;">
                        {{-- Gambar 4 --}}
                        @php
                            $img4 = $product->images->where('sort_order', 3)->first();
                        @endphp
                        <div class="form-group" style="flex: 1; min-width: 220px;">
                            <label class="form-label" style="font-weight: 600;">Gambar 4</label>
                            <div class="avatar-upload-wrap">
                                <div class="avatar-preview" id="previewWrap4"
                                    style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                    @if ($img4)
                                        <img src="{{ Storage::disk('public')->url($img4->image) }}"
                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                    @else
                                        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">📷</div>
                                    @endif
                                </div>
                                <div class="avatar-upload-actions">
                                    <label style="cursor:pointer;">
                                        <input type="file" name="photo_4" id="photo4Input"
                                            accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                            onchange="previewPhoto(this, 'previewWrap4', 'removePhoto4')">
                                        <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;">📷 Ganti</span>
                                    </label>
                                    @if ($img4)
                                        <label class="form-check" style="margin-top:4px;">
                                            <input type="checkbox" name="remove_photo_4" value="1"
                                                class="form-check-input" id="removePhoto4" onchange="handleRemovePhoto(this, 'photo4Input', 'previewWrap4', 'emptyPhotoHtml')">
                                            <span class="form-check-label" style="font-size:11px; color:var(--danger);">Hapus</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                            @error('photo_4')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Gambar 5 --}}
                        @php
                            $img5 = $product->images->where('sort_order', 4)->first();
                        @endphp
                        <div class="form-group" style="flex: 1; min-width: 220px;">
                            <label class="form-label" style="font-weight: 600;">Gambar 5</label>
                            <div class="avatar-upload-wrap">
                                <div class="avatar-preview" id="previewWrap5"
                                    style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                    @if ($img5)
                                        <img src="{{ Storage::disk('public')->url($img5->image) }}"
                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                    @else
                                        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">📷</div>
                                    @endif
                                </div>
                                <div class="avatar-upload-actions">
                                    <label style="cursor:pointer;">
                                        <input type="file" name="photo_5" id="photo5Input"
                                            accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                            onchange="previewPhoto(this, 'previewWrap5', 'removePhoto5')">
                                        <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;">📷 Ganti</span>
                                    </label>
                                    @if ($img5)
                                        <label class="form-check" style="margin-top:4px;">
                                            <input type="checkbox" name="remove_photo_5" value="1"
                                                class="form-check-input" id="removePhoto5" onchange="handleRemovePhoto(this, 'photo5Input', 'previewWrap5', 'emptyPhotoHtml')">
                                            <span class="form-check-label" style="font-size:11px; color:var(--danger);">Hapus</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                            @error('photo_5')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Gambar 6 --}}
                        @php
                            $img6 = $product->images->where('sort_order', 5)->first();
                        @endphp
                        <div class="form-group" style="flex: 1; min-width: 220px;">
                            <label class="form-label" style="font-weight: 600;">Gambar 6</label>
                            <div class="avatar-upload-wrap">
                                <div class="avatar-preview" id="previewWrap6"
                                    style="border-radius: var(--radius-sm); width: 100px; height: 100px;">
                                    @if ($img6)
                                        <img src="{{ Storage::disk('public')->url($img6->image) }}"
                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: var(--radius-sm);">
                                    @else
                                        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">📷</div>
                                    @endif
                                </div>
                                <div class="avatar-upload-actions">
                                    <label style="cursor:pointer;">
                                        <input type="file" name="photo_6" id="photo6Input"
                                            accept="image/jpg,image/jpeg,image/png,image/webp" style="display:none;"
                                            onchange="previewPhoto(this, 'previewWrap6', 'removePhoto6')">
                                        <span class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;">📷 Ganti</span>
                                    </label>
                                    @if ($img6)
                                        <label class="form-check" style="margin-top:4px;">
                                            <input type="checkbox" name="remove_photo_6" value="1"
                                                class="form-check-input" id="removePhoto6" onchange="handleRemovePhoto(this, 'photo6Input', 'previewWrap6', 'emptyPhotoHtml')">
                                            <span class="form-check-label" style="font-size:11px; color:var(--danger);">Hapus</span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                            @error('photo_6')
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
                        <input type="text" id="name" name="name" class="form-control"
                            value="{{ old('name', $product->name) }}" required>
                        @error('name')
                            <div class="form-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="category_id">Kategori <span
                                    style="color:var(--danger)">*</span></label>
                            <select id="category_id" name="category_id" class="form-control" required>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}"
                                        {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
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
                                value="{{ old('sku', $product->sku) }}">
                            @error('sku')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="description">Deskripsi Produk</label>
                        <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
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
                                value="{{ old('price', $product->price) }}" min="0" step="500" required>
                            @error('price')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="original_price">Harga Asli / Coret (Rp)</label>
                            <input type="number" id="original_price" name="original_price" class="form-control"
                                value="{{ old('original_price', $product->original_price) }}" min="0"
                                step="500">
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
                                value="{{ old('stock', $product->stock) }}" min="0" required>
                            @error('stock')
                                <div class="form-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="weight">Berat (gram) <span
                                    style="color:var(--danger)">*</span></label>
                            <input type="number" id="weight" name="weight" class="form-control"
                                value="{{ old('weight', $product->weight) }}" min="1" required>
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
                                value="{{ old('sizes', is_array($product->sizes) ? implode(', ', $product->sizes) : $product->sizes) }}"
                                placeholder="S, M, L, XL">
                            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Pisahkan dengan koma
                            </div>
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
                            <div style="font-size:11px; color:var(--text-muted); margin-top:4px;">Tambahkan warna dan pilih
                                palet hex agar tampil di aplikasi.</div>
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
                                    {{ old('is_active', $product->is_active) ? 'checked' : '' }}>
                                <span class="form-check-label">Produk Aktif (tampil di toko)</span>
                            </label>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <label class="form-check">
                                <input type="checkbox" name="is_recommended" value="1" class="form-check-input"
                                    {{ old('is_recommended', $product->is_recommended) ? 'checked' : '' }}>
                                <span class="form-check-label">Tandai sebagai Rekomendasi</span>
                            </label>
                        </div>
                    </div>

                    {{-- Info produk --}}
                    <div
                        style="background:var(--bg-input); border:1px solid var(--border); border-radius:var(--radius-sm); padding:12px 16px; margin-top:20px; font-size:12px; color:var(--text-muted);">
                        📊 Statistik: Rating <strong style="color:var(--text-secondary);">{{ $product->rating }}</strong>
                        · {{ $product->reviews_count }} ulasan
                        · Slug: <code style="color:var(--accent);">{{ $product->slug }}</code>
                        · Dibuat: {{ $product->created_at->format('d M Y H:i') }}
                    </div>

                    <div style="display:flex; gap:12px; justify-content:flex-end; margin-top:24px;">
                        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="button" class="btn btn-primary" onclick="confirmUpdate('productEditForm', 'Konfirmasi Edit Produk', 'Apakah Anda yakin ingin menyimpan perubahan produk ini?')">💾 Simpan Perubahan</button>
                    </div>

                    {{-- Hidden templates for JavaScript to avoid syntax linter issues --}}
                    <div id="originalPhotoHtml" style="display:none;">
                        @if ($product->main_photo)
                            <img id="avatarPreview" src="{{ Storage::disk('public')->url($product->main_photo) }}" style="width:100%;height:100%;object-fit:cover;border-radius: var(--radius-sm);">
                        @endif
                    </div>
                    <div id="defaultPhotoHtml" style="display:none;">
                        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">
                            @if ($product->category?->slug === 'topi') 🧢
                            @elseif ($product->category?->slug === 'baju') 👕
                            @elseif ($product->category?->slug === 'tumbler') 🥤
                            @else 📦
                            @endif
                        </div>
                    </div>
                    <div id="emptyPhotoHtml" style="display:none;">
                        <div class="avatar-initials" style="border-radius: var(--radius-sm); font-size: 24px;">📷</div>
                    </div>
                    <div id="originalPhoto2Html" style="display:none;">
                        @if ($img2)
                            <img src="{{ Storage::disk('public')->url($img2->image) }}" style="width:100%;height:100%;object-fit:cover;border-radius: var(--radius-sm);">
                        @endif
                    </div>
                    <div id="originalPhoto3Html" style="display:none;">
                        @if ($img3)
                            <img src="{{ Storage::disk('public')->url($img3->image) }}" style="width:100%;height:100%;object-fit:cover;border-radius: var(--radius-sm);">
                        @endif
                    </div>
                    <div id="product-colors-data" data-colors="{{ json_encode(old('colors', $product->colors ?? [])) }}" style="display:none;"></div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previewPhoto(input, targetId, removeCbId) {
            if (input.files && input.files[0]) {
                const reader = new window.FileReader();
                reader.onload = function(e) {
                    const wrap = document.getElementById(targetId);
                    if (wrap) {
                        wrap.innerHTML =
                            `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius: var(--radius-sm);">`;
                    }
                    const removePhotoCb = document.getElementById(removeCbId);
                    if (removePhotoCb) {
                        removePhotoCb.checked = false;
                    }
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function handleRemovePhoto(cb, inputId, targetId, fallbackTemplateId) {
            const photoInput = document.getElementById(inputId);
            if (cb.checked) {
                photoInput.value = '';
                photoInput.disabled = true;
                
                let fallbackIcon = '📷';
                if (fallbackTemplateId === 'defaultPhotoHtml') {
                    fallbackIcon = document.getElementById('defaultPhotoHtml').innerHTML.trim();
                } else {
                    fallbackIcon = document.getElementById('emptyPhotoHtml').innerHTML.trim();
                }
                
                document.getElementById(targetId).innerHTML = fallbackIcon;
            } else {
                photoInput.disabled = false;
                let originalPhoto = '';
                if (inputId === 'mainPhotoInput') {
                    originalPhoto = document.getElementById('originalPhotoHtml').innerHTML.trim();
                } else if (inputId === 'photo2Input') {
                    originalPhoto = document.getElementById('originalPhoto2Html').innerHTML.trim();
                } else if (inputId === 'photo3Input') {
                    originalPhoto = document.getElementById('originalPhoto3Html').innerHTML.trim();
                }
                
                if (originalPhoto) {
                    document.getElementById(targetId).innerHTML = originalPhoto;
                } else {
                    document.getElementById(targetId).innerHTML = document.getElementById('emptyPhotoHtml').innerHTML.trim();
                }
            }
        }

        let colorIndex = 0;

        document.addEventListener("DOMContentLoaded", function() {
            const dataEl = document.getElementById('product-colors-data');
            const oldColors = JSON.parse(dataEl.dataset.colors || '[]');
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
