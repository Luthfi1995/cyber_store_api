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
        <span class="card-title"><i class="bi bi-people-fill"></i> Daftar Pengguna</span>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Tambah User</a>
    </div>

    <div style="padding:14px 20px; border-bottom:1px solid var(--border);">
        <form method="GET" action="{{ route('admin.users.index') }}" class="filter-bar">
            <input type="text" name="search" class="form-control search-input"
                placeholder="Cari nama, email, atau no. telepon..." value="{{ request('search') }}">
            <select name="role" class="form-control">
                <option value="">Semua Role</option>
                <option value="superadmin" {{ request('role')==='superadmin'?'selected':'' }}>Superadmin</option>
                <option value="admin" {{ request('role')==='admin'     ?'selected':'' }}>Admin</option>
                <option value="customer" {{ request('role')==='customer'  ?'selected':'' }}>Customer</option>
            </select>
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status')==='active'  ?'selected':'' }}>Aktif</option>
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
            <div class="empty-state-icon"><i class="bi bi-person-slash"></i></div>
            <h3>Tidak ada pengguna</h3>
            <p>Coba ubah filter pencarian.</p>
        </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pengguna</th>
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
                    <td style="font-size:12px;color:var(--text-muted);">{{ $user->phone ?? '—' }}</td>
                    <td><span class="badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span></td>
                    <td>
                        <span class="badge {{ $user->is_active?'badge-active':'badge-inactive' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                            <iconify-icon icon="{{ $user->is_active ? 'flat-color-icons:checkmark' : 'flat-color-icons:cancel' }}" style="font-size: 13px;"></iconify-icon>
                            {{ $user->is_active?'Aktif':'Nonaktif' }}
                        </span>
                    </td>
                    <td style="font-size:12px;color:var(--text-muted);">{{ $user->created_at->format('d M Y') }}</td>
                    <td>
                        <div class="actions">
                            @if($user->role === 'customer')
                            <a href="{{ route('admin.chats.index', ['search' => $user->email]) }}" class="btn btn-sm btn-icon" style="background-color: #3C3565; color: #ffffff; border-color: #3C3565;" title="Chat Customer">
                                <iconify-icon icon="lucide:message-square" style="font-size: 16px; color: #ffffff;"></iconify-icon>
                            </a>
                            @endif
                            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                            </a>
                            <form method="POST" action="{{ route('admin.users.toggle', $user) }}" style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="btn btn-sm btn-icon {{ $user->is_active?'btn-warning':'btn-success' }}"
                                    title="{{ $user->is_active?'Nonaktifkan':'Aktifkan' }}"
                                    {{ $user->id===auth()->id()?'disabled':'' }}>
                                    <iconify-icon icon="{{ $user->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                                </button>
                            </form>
                            @if($user->role !== 'superadmin' || auth()->user()->role === 'superadmin')
                            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}" style="display:inline;" onsubmit="return confirm('Apakah Anda yakin ingin me-reset password user {{ $user->name }} menjadi \'password\'?')">
                                @csrf
                                <button type="submit"
                                    class="btn btn-secondary btn-sm btn-icon"
                                    title="Reset Password">
                                    <iconify-icon icon="flat-color-icons:key" style="font-size: 16px;"></iconify-icon>
                                </button>
                            </form>
                            @endif
                            @if(auth()->user()->role==='superadmin')
                            <button type="button"
                                class="btn btn-danger btn-sm btn-icon"
                                title="Hapus"
                                data-url="{{ route('admin.users.destroy',$user) }}"
                                data-name="{{ $user->name }}"
                                onclick="confirmDelete(this.dataset.url, this.dataset.name)"
                                {{ $user->id===auth()->id()?'disabled':'' }}>
                                <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                            </button>
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