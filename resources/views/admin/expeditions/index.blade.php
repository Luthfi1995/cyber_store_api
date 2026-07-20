@extends('admin.layouts.app')
@section('title','Kelola Ekspedisi')
@section('page-title','Ekspedisi')
@section('breadcrumb')<span class="breadcrumb-sep">›</span><span>Ekspedisi</span>@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-van-icon lucide-van"><path d="M13 6v5a1 1 0 0 0 1 1h6.102a1 1 0 0 1 .712.298l.898.91a1 1 0 0 1 .288.702V17a1 1 0 0 1-1 1h-3"/><path d="M5 18H3a1 1 0 0 1-1-1V8a2 2 0 0 1 2-2h12c1.1 0 2.1.8 2.4 1.8l1.176 4.2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/><circle cx="7" cy="18" r="2"/></svg> Daftar Ekspedisi</span>
        <a href="{{ route('admin.expeditions.create') }}" class="btn btn-primary">＋ Tambah Ekspedisi</a>
    </div>
    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
        <form method="GET" action="{{ route('admin.expeditions.index') }}" class="filter-bar">
            <input type="text" name="search" class="form-control search-input" placeholder="🔍 Cari ekspedisi..." value="{{ request('search') }}">
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status')==='active'  ?'selected':'' }}>Aktif</option>
                <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.expeditions.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>
    <div class="table-wrapper">
        @if($expeditions->isEmpty())
            <div class="empty-state"><div class="empty-state-icon">🚚</div><h3>Belum ada ekspedisi</h3></div>
        @else
        <table>
            <thead>
                <tr><th>#</th><th>Nama</th><th>Kode</th><th>Layanan</th><th>Biaya Dasar</th><th>Est. Hari</th><th>Order</th><th>Status</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            @foreach($expeditions as $exp)
            <tr>
                <td style="color:var(--text-muted);font-size:12px;">{{ $exp->id }}</td>
                <td style="font-weight:600;color:var(--text-primary);">{{ $exp->name }}</td>
                <td><code style="font-size:12px;color:var(--accent);background:var(--accent-light);padding:2px 8px;border-radius:4px;">{{ $exp->code }}</code></td>
                <td><span class="badge badge-info">{{ $exp->service }}</span></td>
                <td style="font-weight:500;color:var(--text-primary);">Rp {{ number_format($exp->base_cost,0,',','.') }}</td>
                <td>{{ $exp->estimated_days }} hari</td>
                <td>
                    <span style="font-weight:600;color:var(--text-primary);">{{ $exp->orders_count }}</span>
                    <span style="font-size:11px;color:var(--text-muted);"> order</span>
                </td>
                <td>
                    <span class="badge {{ $exp->is_active?'badge-active':'badge-inactive' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                        <iconify-icon icon="{{ $exp->is_active ? 'flat-color-icons:checkmark' : 'flat-color-icons:cancel' }}" style="font-size: 13px;"></iconify-icon>
                        {{ $exp->is_active?'Aktif':'Nonaktif' }}
                    </span>
                </td>
                <td>
                    <div class="actions">
                        <a href="{{ route('admin.expeditions.edit',$exp) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                            <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                        </a>
                        <form method="POST" action="{{ route('admin.expeditions.toggle',$exp) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-icon {{ $exp->is_active?'btn-warning':'btn-success' }}" title="{{ $exp->is_active?'Nonaktifkan':'Aktifkan' }}">
                                <iconify-icon icon="{{ $exp->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm btn-icon" title="Hapus"
                            data-url="{{ route('admin.expeditions.destroy',$exp) }}"
                            data-name="{{ $exp->name }}"
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
    @if($expeditions->hasPages())
    <div class="pagination-wrap">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <span style="font-size:13px;color:var(--text-muted);">{{ $expeditions->firstItem() }}–{{ $expeditions->lastItem() }} dari {{ $expeditions->total() }}</span>
            {{ $expeditions->links('admin.partials.pagination') }}
        </div>
    </div>
    @endif
</div>
@endsection
