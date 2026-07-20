@extends('admin.layouts.app')
@section('title','Kelola Kategori')
@section('page-title','Kategori')
@section('breadcrumb')<span class="breadcrumb-sep">›</span><span>Kategori</span>@endsection
@section('content')
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
    <div class="table-wrapper">
        @if($categories->isEmpty())
            <div class="empty-state"><div class="empty-state-icon">🏷️</div><h3>Belum ada kategori</h3></div>
        @else
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
                <td style="font-size:12px;color:var(--text-muted);max-width:200px;">{{ Str::limit($cat->description,60) }}</td>
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
        @endif
    </div>
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
