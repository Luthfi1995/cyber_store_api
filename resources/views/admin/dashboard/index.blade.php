@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <span>Dashboard</span>
@endsection

@section('content')
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(79,110,247,0.15);">👥</div>
        <div class="stat-info">
            <div class="stat-label">Total Pengguna</div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-sub">{{ $stats['total_customers'] }} customer · {{ $stats['total_admins'] }} admin</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(16,185,129,0.15);">📦</div>
        <div class="stat-info">
            <div class="stat-label">Total Produk</div>
            <div class="stat-value">{{ number_format($stats['total_products']) }}</div>
            <div class="stat-sub">{{ $stats['active_products'] }} aktif · {{ $stats['total_categories'] }} kategori</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(245,158,11,0.15);">🛒</div>
        <div class="stat-info">
            <div class="stat-label">Total Order</div>
            <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
            <div class="stat-sub">{{ $stats['pending_orders'] }} pending · {{ $stats['processing_orders'] }} diproses</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:rgba(6,182,212,0.15);">💰</div>
        <div class="stat-info">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value" style="font-size:20px;">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</div>
            <div class="stat-sub">Dari order selesai</div>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:20px;" class="dashboard-grid">
    {{-- Recent Users --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">👥 Pengguna Terbaru</span>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-wrapper">
            @if($recent_users->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">👤</div>
                    <p>Belum ada pengguna</p>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent_users as $user)
                        <tr>
                            <td>
                                <div style="font-weight:500; color:var(--text-primary);">{{ $user->name }}</div>
                                <div style="font-size:12px; color:var(--text-muted);">{{ $user->email }}</div>
                            </td>
                            <td>
                                <span class="badge badge-{{ $user->role }}">{{ ucfirst($user->role) }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $user->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- Low Stock Products --}}
    <div class="card">
        <div class="card-header">
            <span class="card-title">⚠️ Stok Hampir Habis</span>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary btn-sm">Lihat Semua</a>
        </div>
        <div class="table-wrapper">
            @if($low_stock_products->isEmpty())
                <div class="empty-state">
                    <div class="empty-state-icon">✅</div>
                    <h3>Stok Aman</h3>
                    <p>Semua produk memiliki stok yang cukup</p>
                </div>
            @else
                <table>
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($low_stock_products as $product)
                        <tr>
                            <td>
                                <div style="font-weight:500; color:var(--text-primary);">{{ Str::limit($product->name, 30) }}</div>
                                <div style="font-size:12px; color:var(--text-muted);">{{ $product->sku }}</div>
                            </td>
                            <td>
                                <span style="color:{{ $product->stock <= 5 ? 'var(--danger)' : 'var(--warning)' }}; font-weight:600;">
                                    {{ $product->stock }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning btn-sm">Edit</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>

{{-- Recent Orders --}}
<div class="card">
    <div class="card-header">
        <span class="card-title">🛒 Order Terbaru</span>
    </div>
    <div class="table-wrapper">
        @if($recent_orders->isEmpty())
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <h3>Belum Ada Order</h3>
                <p>Order dari pelanggan akan muncul di sini</p>
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Pelanggan</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_orders as $order)
                    <tr>
                        <td style="font-family:monospace; color:var(--accent);">#{{ $order->id }}</td>
                        <td>{{ $order->user?->name ?? '-' }}</td>
                        <td>
                            @php
                                $statusColors = [
                                    'pending_payment' => 'badge-warning',
                                    'paid'            => 'badge-active',
                                    'packed'          => 'badge-active',
                                    'shipped'         => 'badge-recommended',
                                    'arrived'         => 'badge-recommended',
                                    'completed'       => 'badge-active',
                                    'cancelled'       => 'badge-inactive',
                                ];
                                $statusLabels = [
                                    'pending_payment' => 'Menunggu Bayar',
                                    'paid'            => 'Dibayar',
                                    'packed'          => 'Dikemas',
                                    'shipped'         => 'Dikirim',
                                    'arrived'         => 'Tiba',
                                    'completed'       => 'Selesai',
                                    'cancelled'       => 'Dibatalkan',
                                ];
                            @endphp
                            <span class="badge {{ $statusColors[$order->status] ?? 'badge-inactive' }}">
                                {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                            </span>
                        </td>
                        <td>Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                        <td style="font-size:12px; color:var(--text-muted);">{{ $order->created_at->format('d M Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>

<style>
@media (max-width: 768px) {
    .dashboard-grid { grid-template-columns: 1fr !important; }
}
</style>
@endsection
