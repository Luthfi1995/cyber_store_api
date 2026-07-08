@extends('admin.layouts.app')
@section('title','Tambah Kategori')
@section('page-title','Tambah Kategori')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span><a href="{{ route('admin.categories.index') }}">Kategori</a>
    <span class="breadcrumb-sep">›</span><span>Tambah</span>
@endsection
@section('content')
<div style="max-width:600px;">
<div class="card">
    <div class="card-header">
        <span class="card-title">🏷️ Tambah Kategori</span>
        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">← Kembali</a>
    </div>
    <div class="card-body">
        <form id="categoryCreateForm" method="POST" action="{{ route('admin.categories.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label" for="name">Nama Kategori <span style="color:var(--danger)">*</span></label>
                <input type="text" id="name" name="name" class="form-control"
                    value="{{ old('name') }}" placeholder="Misal: Topi, Baju, Tumbler" required>
                <div class="form-hint">Slug akan di-generate otomatis dari nama.</div>
                @error('name')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-label" for="description">Deskripsi</label>
                <textarea id="description" name="description" class="form-control" rows="3"
                    placeholder="Deskripsi singkat kategori...">{{ old('description') }}</textarea>
                @error('description')<div class="form-error">{{ $message }}</div>@enderror
            </div>
            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input"
                        {{ old('is_active','1') ? 'checked' : '' }}>
                    <span class="form-check-label">Kategori Aktif</span>
                </label>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Batal</a>
                <button type="button" class="btn btn-primary" onclick="confirmCreate('categoryCreateForm', 'Konfirmasi Tambah Kategori', 'Apakah Anda yakin ingin menyimpan kategori baru ini?')">💾 Simpan</button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
