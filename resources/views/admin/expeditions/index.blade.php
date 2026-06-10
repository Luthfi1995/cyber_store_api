@extends('admin.layouts.app')
@section('title','Kelola Ekspedisi')
@section('page-title','Ekspedisi')
@section('breadcrumb')<span class="breadcrumb-sep">›</span><span>Ekspedisi</span>@endsection
@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">🚚 Daftar Ekspedisi</span>
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
                <td><span class="badge {{ $exp->is_active?'badge-active':'badge-inactive' }}">{{ $exp->is_active?'Aktif':'Nonaktif' }}</span></td>
                <td>
                    <div class="actions">
                        <a href="{{ route('admin.expeditions.edit',$exp) }}" class="btn btn-secondary btn-sm btn-icon">✏️</a>
                        <form method="POST" action="{{ route('admin.expeditions.toggle',$exp) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-icon {{ $exp->is_active?'btn-warning':'btn-success' }}">
                                {{ $exp->is_active?'🚫':'✅' }}
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm btn-icon"
                            onclick="confirmDelete('{{ route('admin.expeditions.destroy',$exp) }}','{{ addslashes($exp->name) }}')">🗑️</button>
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
