@extends('admin.layouts.app')
@section('title','Kelola Kategori')
@section('page-title','Kategori')
@section('breadcrumb')<span class="breadcrumb-sep">›</span><span>Kategori</span>@endsection
@section('content')
<style>
    .desktop-table-container { display: block; }
    .mobile-category-grid { display: none; padding: 16px; gap: 14px; flex-direction: column; }
    .mobile-category-card {
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    @media (max-width: 768px) {
        .desktop-table-container { display: none !important; }
        .mobile-category-grid { display: flex !important; }
    }
</style>

<div class="card">
    <div class="card-header">
        <span class="card-title">🏷️ Daftar Kategori</span>
        <div style="display: flex; gap: 8px; align-items: center;">
            {{-- Tombol flush cache kategori di Redis agar Flutter langsung segar --}}
            <form method="POST" action="{{ route('admin.cache.flush-categories') }}" style="margin: 0;" onsubmit="return confirm('Bersihkan cache kategori di server? Flutter akan langsung menampilkan data terbaru.')">
                @csrf
                <button type="submit" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; background-color: #F59E0B; border-color: #F59E0B; color: white;">
                    🔄 Bersihkan Cache
                </button>
            </form>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">＋ Tambah Kategori</a>
        </div>
    </div>
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
        <form method="GET" action="{{ route('admin.categories.index') }}" class="filter-bar">
            <input type="text" name="search" class="form-control search-input" placeholder="🔍 Cari kategori..." value="{{ request('search') }}">
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status')==='active'  ?'selected':'' }}>Aktif</option>
                <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>
    @if($categories->isEmpty())
        <div class="empty-state"><div class="empty-state-icon">🏷️</div><h3>Belum ada kategori</h3></div>
    @else
        <!-- Desktop Table View (>768px) -->
        <div class="table-wrapper desktop-table-container">
            <table>
                <thead>
                    <tr><th>#</th><th>Nama Kategori</th><th>Slug</th><th>Deskripsi</th><th>Produk</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                @foreach($categories as $cat)
                <tr>
                    <td style="color:var(--text-muted);font-size:12px;">{{ $cat->id }}</td>
                    <td><div style="font-weight:600;color:var(--text-primary);">{{ $cat->name }}</div></td>
                    <td><code style="font-size:12px;color:var(--accent);background:var(--accent-light);padding:2px 8px;border-radius:4px;">{{ $cat->slug }}</code></td>
                    <td style="font-size:12px;color:var(--text-muted);max-width:200px;">{{ \Illuminate\Support\Str::limit($cat->description,60) }}</td>
                    <td>
                        <span style="font-weight:600;color:var(--text-primary);">{{ $cat->products_count }}</span>
                        <span style="font-size:11px;color:var(--text-muted);"> produk</span>
                    </td>
                    <td>
                        <span class="badge {{ $cat->is_active?'badge-active':'badge-inactive' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="{{ $cat->is_active ? 'flat-color-icons:checkmark' : 'flat-color-icons:cancel' }}" style="font-size: 13px;"></iconify-icon>
                            {{ $cat->is_active?'Aktif':'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.categories.edit',$cat) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                            </a>
                            <form method="POST" action="{{ route('admin.categories.toggle',$cat) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-sm btn-icon {{ $cat->is_active?'btn-warning':'btn-success' }}" title="{{ $cat->is_active?'Nonaktifkan':'Aktifkan' }}">
                                    <iconify-icon icon="{{ $cat->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                                </button>
                            </form>
                            <button type="button" class="btn btn-danger btn-sm btn-icon" title="Hapus"
                                data-url="{{ route('admin.categories.destroy',$cat) }}"
                                data-name="{{ $cat->name }}"
                                onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                                <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Category Card View (<=768px) -->
        <div class="mobile-category-grid">
            @foreach($categories as $cat)
            <div class="mobile-category-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="font-weight: 800; font-size: 15px; color: var(--text-primary);">
                        🏷️ {{ $cat->name }}
                    </div>
                    <span class="badge {{ $cat->is_active ? 'badge-active' : 'badge-inactive' }}">
                        {{ $cat->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div style="font-size: 12px; margin-bottom: 8px;">
                    <code style="color: var(--accent); background: var(--accent-light); padding: 2px 8px; border-radius: 4px;">{{ $cat->slug }}</code>
                </div>
                @if($cat->description)
                <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">
                    {{ \Illuminate\Support\Str::limit($cat->description, 80) }}
                </div>
                @endif
                <div style="background: var(--bg-input, #f8fafc); padding: 10px 12px; border-radius: 8px; font-size: 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <div>Jumlah Produk:</div>
                    <div style="font-weight: 800; color: var(--text-primary); font-size: 13px;">
                        📦 {{ $cat->products_count }} Produk
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; align-items: center; gap: 6px;">
                    <a href="{{ route('admin.categories.edit',$cat) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                        <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                    </a>
                    <form method="POST" action="{{ route('admin.categories.toggle',$cat) }}" style="display:inline;">
                        @csrf @method('PATCH')
                        <button type="submit" class="btn btn-sm btn-icon {{ $cat->is_active ? 'btn-warning' : 'btn-success' }}" title="{{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                            <iconify-icon icon="{{ $cat->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger btn-sm btn-icon" title="Hapus"
                        data-url="{{ route('admin.categories.destroy',$cat) }}"
                        data-name="{{ $cat->name }}"
                        onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                        <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    @endif
    @if($categories->hasPages())
    <div class="pagination-wrap">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <span style="font-size:13px;color:var(--text-muted);">{{ $categories->firstItem() }}–{{ $categories->lastItem() }} dari {{ $categories->total() }}</span>
            {{ $categories->links('admin.partials.pagination') }}
        </div>
    </div>
    @endif
</div>
@endsection
