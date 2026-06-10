@extends('admin.layouts.app')

@section('title', 'Kelola Pengguna')
@section('page-title', 'Kelola Pengguna')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <span>Pengguna</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">👥 Daftar Pengguna</span>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">＋ Tambah User</a>
    </div>

    <div style="padding:14px 20px; border-bottom:1px solid var(--border);">
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar">
            <input type="text" name="search" class="form-control search-input"
                placeholder="🔍 Cari nama, email, NIM..." value="{{ request('search') }}">
            <select name="role" class="form-control">
                <option value="">Semua Role</option>
                <option value="superadmin" {{ request('role')==='superadmin'?'selected':'' }}>Superadmin</option>
                <option value="admin"      {{ request('role')==='admin'     ?'selected':'' }}>Admin</option>
                <option value="customer"   {{ request('role')==='customer'  ?'selected':'' }}>Customer</option>
            </select>
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="active"   {{ request('status')==='active'  ?'selected':'' }}>Aktif</option>
                <option value="inactive" {{ request('status')==='inactive'?'selected':'' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['search','role','status']))
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrapper">
        @if($users->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">👤</div>
                <h3>Tidak ada pengguna</h3>
                <p>Coba ubah filter pencarian.</p>
            </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pengguna</th>
                    <th>NIM</th>
                    <th>Telepon</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
            @foreach($users as $user)
            <tr>
                <td style="color:var(--text-muted);font-size:12px;">{{ $user->id }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        {{-- Avatar --}}
                        <div style="width:38px;height:38px;border-radius:50%;overflow:hidden;flex-shrink:0;border:2px solid var(--border);">
                            @if($user->photo)
                                <img src="{{ Storage::disk('public')->url($user->photo) }}"
                                     style="width:100%;height:100%;object-fit:cover;"
                                     alt="{{ $user->name }}">
                            @else
                                <div style="width:100%;height:100%;background:linear-gradient(135deg,#4f6ef7,#7c3aed);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:14px;color:#fff;">
                                    {{ strtoupper(substr($user->name,0,1)) }}
                                </div>
                            @endif
                        </div>
                        <div>
                            <div style="font-weight:600;color:var(--text-primary);">{{ $user->name }}</div>
                            <div style="font-size:12px;color:var(--text-muted);">{{ $user->email }}</div>
                        </div>
                    </div>
                </td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $user->nim ?? '—' }}</td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $user->phone ?? '—' }}</td>
                <td><span class="badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                <td>
                    <span class="badge {{ $user->is_active?'badge-active':'badge-inactive' }}">
                        {{ $user->is_active?'Aktif':'Nonaktif' }}
                    </span>
                </td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $user->created_at->format('d M Y') }}</td>
                <td>
                    <div class="actions">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">✏️</a>
                        <form method="POST" action="{{ route('admin.users.toggle', $user) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="btn btn-sm btn-icon {{ $user->is_active?'btn-warning':'btn-success' }}"
                                title="{{ $user->is_active?'Nonaktifkan':'Aktifkan' }}"
                                {{ $user->id===auth()->id()?'disabled':'' }}>
                                {{ $user->is_active?'🚫':'✅' }}
                            </button>
                        </form>
                        @if(auth()->user()->role==='superadmin')
                            <button type="button"
                                class="btn btn-danger btn-sm btn-icon"
                                title="Hapus"
                                onclick="confirmDelete('{{ route('admin.users.destroy',$user) }}','{{ addslashes($user->name) }}')"
                                {{ $user->id===auth()->id()?'disabled':'' }}>🗑️</button>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @if($users->hasPages())
    <div class="pagination-wrap">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;">
            <span style="font-size:13px;color:var(--text-muted);">
                Menampilkan {{ $users->firstItem() }}–{{ $users->lastItem() }} dari {{ $users->total() }} pengguna
            </span>
            {{ $users->links('admin.partials.pagination') }}
        </div>
    </div>
    @endif
</div>
@endsection
