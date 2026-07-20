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
            <form id="settingsForm" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
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
                        $logoUrl = $logoSetting ? Storage::disk('public')->url($logoSetting) : asset('/assets/img/logo-cyberstore.jpg');
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

                <hr style="margin: 32px 0; border: 0; border-top: 1px solid var(--border-color, #E2E8F0);">

                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; font-weight: 700; color: #0D47A1; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                        <iconify-icon icon="flat-color-icons:headset" style="font-size: 22px;"></iconify-icon> Kontang CS & Pusat Bantuan App
                    </h3>
                    <p style="font-size: 13px; color: var(--text-muted); margin: 0;">Kontak dan daftar pertanyaan umum (FAQ) di bawah ini akan langsung tampil di halaman Pusat Bantuan aplikasi Flutter.</p>
                </div>

                {{-- WhatsApp CS --}}
                <div class="form-group">
                    <label class="form-label" for="help_whatsapp">No. WhatsApp Customer Service</label>
                    <input type="text" id="help_whatsapp" name="help_whatsapp" class="form-control"
                        value="{{ old('help_whatsapp', $helpWhatsapp) }}" placeholder="Contoh: 628123456789">
                    <div class="form-hint">Format internasional tanpa simbol (contoh: 628123456789).</div>
                </div>

                {{-- Email Support --}}
                <div class="form-group">
                    <label class="form-label" for="help_email">Email Customer Support</label>
                    <input type="email" id="help_email" name="help_email" class="form-control"
                        value="{{ old('help_email', $helpEmail) }}" placeholder="Contoh: cs@cyberstore.id">
                </div>

                {{-- Telepon CS --}}
                <div class="form-group">
                    <label class="form-label" for="help_phone">No. Telepon Support</label>
                    <input type="text" id="help_phone" name="help_phone" class="form-control"
                        value="{{ old('help_phone', $helpPhone) }}" placeholder="Contoh: (021) 7867868">
                </div>

                {{-- FAQ Repeater --}}
                <div style="margin-top: 24px; margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <h4 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <iconify-icon icon="flat-color-icons:faq" style="font-size: 18px;"></iconify-icon> Daftar Pertanyaan Umum (FAQ)
                        </h4>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addFaqRow()" style="font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="flat-color-icons:plus"></iconify-icon> Tambah FAQ
                        </button>
                    </div>
                </div>

                <div id="faqListContainer" style="display: flex; flex-direction: column; gap: 12px;">
                    @foreach($helpFaqs as $idx => $faq)
                    <div class="faq-item-card" style="background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-weight: 700; font-size: 12px; color: #0D47A1;">FAQ #<span class="faq-num">{{ $idx + 1 }}</span></span>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeFaqRow(this)" style="padding: 2px 8px; font-size: 11px;">
                                <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus
                            </button>
                        </div>
                        <div class="form-group" style="margin-bottom: 8px;">
                            <input type="text" name="faqs[{{ $idx }}][question]" class="form-control" value="{{ $faq['question'] ?? '' }}" placeholder="Pertanyaan..." required>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <textarea name="faqs[{{ $idx }}][answer]" class="form-control" rows="2" placeholder="Jawaban..." required>{{ $faq['answer'] ?? '' }}</textarea>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Panduan Aplikasi Repeater --}}
                <hr style="margin: 32px 0; border: 0; border-top: 1px solid var(--border-color, #E2E8F0);">

                <div style="margin-bottom: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <h4 style="font-size: 14px; font-weight: 700; color: #1E293B; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <iconify-icon icon="flat-color-icons:rules" style="font-size: 20px;"></iconify-icon> Bab & Langkah Panduan Aplikasi
                        </h4>
                        <button type="button" class="btn btn-sm btn-secondary" onclick="addGuideSectionRow()" style="font-size: 12px; display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="flat-color-icons:plus"></iconify-icon> Tambah Bab Panduan
                        </button>
                    </div>
                </div>

                <div id="guideListContainer" style="display: flex; flex-direction: column; gap: 16px;">
                    @foreach($helpGuides as $gIdx => $guide)
                    <div class="guide-item-card" style="background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 12px; padding: 16px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-weight: 700; font-size: 13px; color: #0D47A1;">Bab #<span class="guide-num">{{ $gIdx + 1 }}</span></span>
                            <button type="button" class="btn btn-sm btn-danger" onclick="removeGuideSectionRow(this)" style="padding: 2px 8px; font-size: 11px;">
                                <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus Bab
                            </button>
                        </div>
                        <div class="form-group" style="margin-bottom: 8px;">
                            <label style="font-size: 12px; font-weight: 600;">Judul Bab Panduan</label>
                            <input type="text" name="guides[{{ $gIdx }}][title]" class="form-control" value="{{ $guide['title'] ?? '' }}" placeholder="Judul Bab (contoh: Cara Berbelanja & Checkout)" required>
                        </div>
                        <div class="form-group" style="margin-bottom: 8px;">
                            <label style="font-size: 12px; font-weight: 600;">Ringkasan Deskripsi</label>
                            <input type="text" name="guides[{{ $gIdx }}][description]" class="form-control" value="{{ $guide['description'] ?? '' }}" placeholder="Deskripsi singkat bab panduan...">
                        </div>
                        <div class="form-group" style="margin-bottom: 12px;">
                            <label style="font-size: 12px; font-weight: 600;">Gambar Banner / Ilustrasi Bab</label>
                            <div style="display: flex; gap: 10px; align-items: center; margin-bottom: 6px;">
                                @if(!empty($guide['image']))
                                <img src="{{ $guide['image'] }}" style="width: 80px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid #CBD5E1;">
                                @endif
                                <input type="file" name="guides[{{ $gIdx }}][image_file]" class="form-control form-control-sm" accept="image/*" style="font-size: 11px;">
                            </div>
                            <input type="text" name="guides[{{ $gIdx }}][image]" class="form-control form-control-sm" value="{{ $guide['image'] ?? '' }}" placeholder="Atau paste URL Gambar (http/https)...">
                        </div>

                        <div style="background: white; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; margin-top: 10px;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                <span style="font-size: 12px; font-weight: 700; color: #475569;">Langkah-Langkah:</span>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="addStepRow(this, {{ $gIdx }})" style="font-size: 11px; padding: 2px 6px;">
                                    + Tambah Langkah
                                </button>
                            </div>
                            <div class="steps-container" style="display: flex; flex-direction: column; gap: 8px;">
                                @foreach(($guide['steps'] ?? []) as $sIdx => $step)
                                <div class="step-item-card" style="display: flex; gap: 8px; align-items: flex-start; background: #F1F5F9; padding: 8px; border-radius: 6px;">
                                    <span style="font-size: 11px; font-weight: 700; color: #0D47A1; margin-top: 6px;">{{ $sIdx + 1 }}.</span>
                                    <div style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
                                        <input type="text" name="guides[{{ $gIdx }}][steps][{{ $sIdx }}][title]" class="form-control form-control-sm" value="{{ $step['title'] ?? '' }}" placeholder="Judul Langkah..." required>
                                        <input type="text" name="guides[{{ $gIdx }}][steps][{{ $sIdx }}][desc]" class="form-control form-control-sm" value="{{ $step['desc'] ?? '' }}" placeholder="Penjelasan Langkah...">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger" onclick="removeStepRow(this)" style="padding: 2px 6px; font-size: 10px; margin-top: 4px;">✕</button>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:24px;">
                    <button type="button" class="btn btn-primary" onclick="confirmUpdate('settingsForm', 'Konfirmasi Simpan Pengaturan', 'Apakah Anda yakin ingin menyimpan perubahan pengaturan toko, FAQ, dan Panduan Aplikasi ini?')">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function addFaqRow() {
        const container = document.getElementById('faqListContainer');
        const count = container.children.length;
        const div = document.createElement('div');
        div.className = 'faq-item-card';
        div.style = 'background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 10px; padding: 14px;';
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                <span style="font-weight: 700; font-size: 12px; color: #0D47A1;">FAQ #<span class="faq-num">${count + 1}</span></span>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeFaqRow(this)" style="padding: 2px 8px; font-size: 11px;">
                    <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus
                </button>
            </div>
            <div class="form-group" style="margin-bottom: 8px;">
                <input type="text" name="faqs[${count}][question]" class="form-control" placeholder="Pertanyaan..." required>
            </div>
            <div class="form-group" style="margin-bottom: 0;">
                <textarea name="faqs[${count}][answer]" class="form-control" rows="2" placeholder="Jawaban..." required></textarea>
            </div>
        `;
        container.appendChild(div);
    }

    function removeFaqRow(btn) {
        btn.closest('.faq-item-card').remove();
        document.querySelectorAll('#faqListContainer .faq-item-card').forEach((card, idx) => {
            card.querySelector('.faq-num').innerText = idx + 1;
        });
    }

    function addGuideSectionRow() {
        const container = document.getElementById('guideListContainer');
        const gIdx = container.children.length;
        const div = document.createElement('div');
        div.className = 'guide-item-card';
        div.style = 'background: #F8FAFC; border: 1px solid #CBD5E1; border-radius: 12px; padding: 16px;';
        div.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                <span style="font-weight: 700; font-size: 13px; color: #0D47A1;">Bab #<span class="guide-num">${gIdx + 1}</span></span>
                <button type="button" class="btn btn-sm btn-danger" onclick="removeGuideSectionRow(this)" style="padding: 2px 8px; font-size: 11px;">
                    <iconify-icon icon="fluent-emoji-flat:wastebasket"></iconify-icon> Hapus Bab
                </button>
            </div>
            <div class="form-group" style="margin-bottom: 8px;">
                <label style="font-size: 12px; font-weight: 600;">Judul Bab Panduan</label>
                <input type="text" name="guides[${gIdx}][title]" class="form-control" placeholder="Judul Bab..." required>
            </div>
            <div class="form-group" style="margin-bottom: 8px;">
                <label style="font-size: 12px; font-weight: 600;">Ringkasan Deskripsi</label>
                <input type="text" name="guides[${gIdx}][description]" class="form-control" placeholder="Deskripsi singkat bab...">
            </div>
            <div class="form-group" style="margin-bottom: 12px;">
                <label style="font-size: 12px; font-weight: 600;">Gambar Banner / Ilustrasi Bab</label>
                <input type="file" name="guides[${gIdx}][image_file]" class="form-control form-control-sm" accept="image/*" style="font-size: 11px; margin-bottom: 4px;">
                <input type="text" name="guides[${gIdx}][image]" class="form-control form-control-sm" placeholder="Atau paste URL Gambar (http/https)...">
            </div>

            <div style="background: white; border: 1px solid #E2E8F0; border-radius: 8px; padding: 12px; margin-top: 10px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; color: #475569;">Langkah-Langkah:</span>
                    <button type="button" class="btn btn-sm btn-secondary" onclick="addStepRow(this, ${gIdx})" style="font-size: 11px; padding: 2px 6px;">
                        + Tambah Langkah
                    </button>
                </div>
                <div class="steps-container" style="display: flex; flex-direction: column; gap: 8px;">
                </div>
            </div>
        `;
        container.appendChild(div);
        addStepRow(div.querySelector('.btn-secondary'), gIdx);
    }

    function removeGuideSectionRow(btn) {
        btn.closest('.guide-item-card').remove();
        document.querySelectorAll('#guideListContainer .guide-item-card').forEach((card, idx) => {
            card.querySelector('.guide-num').innerText = idx + 1;
        });
    }

    function addStepRow(btn, gIdx) {
        const stepsContainer = btn.closest('.guide-item-card').querySelector('.steps-container');
        const sIdx = stepsContainer.children.length;
        const div = document.createElement('div');
        div.className = 'step-item-card';
        div.style = 'display: flex; gap: 8px; align-items: flex-start; background: #F1F5F9; padding: 8px; border-radius: 6px;';
        div.innerHTML = `
            <span style="font-size: 11px; font-weight: 700; color: #0D47A1; margin-top: 6px;">${sIdx + 1}.</span>
            <div style="flex: 1; display: flex; flex-direction: column; gap: 4px;">
                <input type="text" name="guides[${gIdx}][steps][${sIdx}][title]" class="form-control form-control-sm" placeholder="Judul Langkah..." required>
                <input type="text" name="guides[${gIdx}][steps][${sIdx}][desc]" class="form-control form-control-sm" placeholder="Penjelasan Langkah...">
            </div>
            <button type="button" class="btn btn-sm btn-danger" onclick="removeStepRow(this)" style="padding: 2px 6px; font-size: 10px; margin-top: 4px;">✕</button>
        `;
        stepsContainer.appendChild(div);
    }

    function removeStepRow(btn) {
        const container = btn.closest('.steps-container');
        btn.closest('.step-item-card').remove();
        container.querySelectorAll('.step-item-card').forEach((card, idx) => {
            card.querySelector('span').innerText = (idx + 1) + '.';
        });
    }
</script>
@endpush
@endsection