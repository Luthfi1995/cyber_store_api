@extends('admin.layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<span>Ikhtisar</span>
@endsection

@section('content')
<!-- Notice Banner -->
<div class="notice-banner">
    <div class="notice-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect>
            <line x1="8" y1="21" x2="16" y2="21"></line>
            <line x1="12" y1="17" x2="12" y2="21"></line>
        </svg>
    </div>
    <div style="font-size: 13px; color: #334155; line-height: 1.5;">
        <strong>Selamat Datang Kembali, Admin!</strong> Berikut adalah ringkasan aktivitas penjualan, stok barang, serta pengguna toko {{ \App\Models\Setting::get('store_name', 'BSI Cyber Store') }} hari ini.
    </div>
</div>

<!-- Stats Summary Grid (Soft UI Dashboard Pro Style) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 24px;">
    {{-- Sales / Pendapatan Card --}}
    <div style="background: var(--bg-card); border-radius: 1rem; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div style="font-size: 12.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Penjualan</div>
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Bulan Ini</div>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}
        </div>
        <div style="font-size: 12px; font-weight: 700; color: #82d616; display: flex; align-items: center; gap: 4px;">
            <span>+55%</span>
            <span style="color: var(--text-muted); font-weight: 500;">dibanding bulan lalu</span>
        </div>
    </div>

    {{-- Customers Card --}}
    <div style="background: var(--bg-card); border-radius: 1rem; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div style="font-size: 12.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Pelanggan</div>
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Bulan Ini</div>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            {{ number_format($stats['total_users']) }}
        </div>
        <div style="font-size: 12px; font-weight: 700; color: #82d616; display: flex; align-items: center; gap: 4px;">
            <span>+12%</span>
            <span style="color: var(--text-muted); font-weight: 500;">dibanding bulan lalu</span>
        </div>
    </div>

    {{-- Orders Card --}}
    <div style="background: var(--bg-card); border-radius: 1rem; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div style="font-size: 12.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Pesanan</div>
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Bulan Ini</div>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            {{ number_format($stats['total_orders']) }}
        </div>
        <div style="font-size: 12px; font-weight: 700; color: #82d616; display: flex; align-items: center; gap: 4px;">
            <span>+8%</span>
            <span style="color: var(--text-muted); font-weight: 500;">dibanding bulan lalu</span>
        </div>
    </div>

    {{-- Active Products Card --}}
    <div style="background: var(--bg-card); border-radius: 1rem; padding: 20px; box-shadow: var(--shadow); border: 1px solid var(--border); position: relative; overflow: hidden;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
            <div style="font-size: 12.5px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Produk</div>
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted);">Bulan Ini</div>
        </div>
        <div style="font-size: 22px; font-weight: 800; color: var(--text-primary); margin-bottom: 4px; letter-spacing: -0.5px;">
            {{ number_format($stats['total_products']) }}
        </div>
        <div style="font-size: 12px; font-weight: 700; color: #82d616; display: flex; align-items: center; gap: 4px;">
            <span>+15%</span>
            <span style="color: var(--text-muted); font-weight: 500;">dibanding bulan lalu</span>
        </div>
    </div>
</div>

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px;" class="dashboard-grid">
    {{-- Recent Users Card --}}
    <div class="table-card" style="margin-bottom: 0;">
        <div class="table-card-header">
            <div style="font-weight: 800; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <span>👥</span> Pengguna Terbaru
            </div>
            <a href="{{ route('admin.users.index') }}" style="font-size: 12px; font-weight: 700; color: #0B023E; text-decoration: none; background: #f1f5f9; padding: 6px 12px; border-radius: 8px;">Lihat Semua ›</a>
        </div>
        <div style="overflow-x: auto;">
            @if($recent_users->isEmpty())
            <div style="text-align: center; padding: 32px; color: #94a3b8;">Belum ada pengguna</div>
            @else
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 11.5px; color: #64748b; text-transform: uppercase;">
                        <th style="padding: 12px 16px;">Nama & Email</th>
                        <th style="padding: 12px 16px;">Role</th>
                        <th style="padding: 12px 16px;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_users as $user)
                    <tr class="table-row-custom" style="border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 700; color: #0f172a;">{{ $user->name }}</div>
                            <div style="font-size: 11.5px; color: #64748b;">{{ $user->email }}</div>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span class="badge-type {{ $user->role === 'admin' ? 'badge-promo' : 'badge-info' }}">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span class="badge-type {{ $user->is_active ? 'badge-success-custom' : 'badge-danger-custom' }}">
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

    {{-- Low Stock Products Card --}}
    <div class="table-card" style="margin-bottom: 0;">
        <div class="table-card-header">
            <div style="font-weight: 800; font-size: 15px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <span>⚠️</span> Stok Hampir Habis
            </div>
            <a href="{{ route('admin.products.index') }}" style="font-size: 12px; font-weight: 700; color: #0B023E; text-decoration: none; background: #f1f5f9; padding: 6px 12px; border-radius: 8px;">Lihat Semua ›</a>
        </div>
        <div style="overflow-x: auto;">
            @if($low_stock_products->isEmpty())
            <div style="text-align: center; padding: 32px; color: #16a34a;">
                <div style="font-size: 24px; margin-bottom: 4px;">✅</div>
                <div style="font-weight: 700;">Stok Aman</div>
                <div style="font-size: 12px; color: #64748b;">Semua produk memiliki stok cukup</div>
            </div>
            @else
            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 11.5px; color: #64748b; text-transform: uppercase;">
                        <th style="padding: 12px 16px;">Nama Produk</th>
                        <th style="padding: 12px 16px;">Sisa Stok</th>
                        <th style="padding: 12px 16px; text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($low_stock_products as $product)
                    <tr class="table-row-custom" style="border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                        <td style="padding: 12px 16px;">
                            <div style="font-weight: 700; color: #0f172a;">{{ \Illuminate\Support\Str::limit($product->name, 28) }}</div>
                            <div style="font-size: 11.5px; color: #64748b;">SKU: {{ $product->sku }}</div>
                        </td>
                        <td style="padding: 12px 16px;">
                            <span style="font-weight: 800;" class="{{ $product->stock <= 5 ? 'text-danger' : 'text-warning' }}">
                                {{ $product->stock }} Pcs
                            </span>
                        </td>
                        <td style="padding: 12px 16px; text-align: right;">
                            <a href="{{ route('admin.products.edit', $product) }}" style="background: #fef3c7; color: #b45309; padding: 4px 10px; border-radius: 6px; font-size: 11.5px; font-weight: 700; text-decoration: none;">Restok</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

{{-- Recent Orders Table Card --}}
<div class="table-card">
    <div class="table-card-header">
        <div style="font-weight: 800; font-size: 16px; color: #0f172a; display: flex; align-items: center; gap: 8px;">
            <span>🛒</span> Pesanan Terbaru
        </div>
        <a href="{{ route('admin.orders.index') }}" style="font-size: 12px; font-weight: 700; color: #0B023E; text-decoration: none; background: #f1f5f9; padding: 6px 12px; border-radius: 8px;">Semua Pesanan ›</a>
    </div>
    <div style="overflow-x: auto;" class="desktop-table-container">
        @if($recent_orders->isEmpty())
        <div style="text-align: center; padding: 40px; color: #94a3b8;">
            <div style="font-size: 32px; margin-bottom: 8px;">📋</div>
            <div style="font-weight: 700; color: #475569;">Belum Ada Pesanan</div>
            <div style="font-size: 12.5px;">Pesanan dari pelanggan akan otomatis tampil di sini</div>
        </div>
        @else
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1.5px solid #e2e8f0; font-size: 12px; color: #64748b; text-transform: uppercase;">
                    <th style="padding: 14px 20px;">ID Pesanan</th>
                    <th style="padding: 14px 20px;">Pelanggan</th>
                    <th style="padding: 14px 20px;">Status Pembayaran / Pengiriman</th>
                    <th style="padding: 14px 20px;">Total Bayar</th>
                    <th style="padding: 14px 20px;">Tanggal Pesanan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recent_orders as $order)
                <tr class="table-row-custom" style="border-bottom: 1px solid #f1f5f9; font-size: 13.5px;">
                    <td style="padding: 14px 20px; font-family: monospace; font-weight: 800; color: #0B023E;">
                        #{{ $order->id }}
                    </td>
                    <td style="padding: 14px 20px; font-weight: 700; color: #0f172a;">
                        {{ $order->user?->name ?? '-' }}
                    </td>
                    <td style="padding: 14px 20px;">
                        @php
                        $statusLabels = [
                        'pending_payment' => 'Menunggu Bayar',
                        'paid' => 'Dibayar',
                        'packed' => 'Dikemas',
                        'shipped' => 'Dikirim',
                        'arrived' => 'Tiba',
                        'completed' => 'Selesai',
                        'cancelled' => 'Dibatalkan',
                        ];
                        $badgeClass = match($order->status) {
                        'completed', 'paid' => 'badge-success-custom',
                        'pending_payment', 'packed', 'shipped' => 'badge-warning-custom',
                        default => 'badge-danger-custom'
                        };
                        @endphp
                        <span class="badge-type {{ $badgeClass }}">
                            {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                        </span>
                    </td>
                    <td style="padding: 14px 20px; font-weight: 800; color: #0f172a;">
                        Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                    </td>
                    <td style="padding: 14px 20px; color: #64748b; font-size: 12.5px;">
                        📅 {{ $order->created_at->format('d M Y, H:i') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    <!-- Mobile Card View for Recent Orders (<=768px) -->
    <div class="mobile-dashboard-grid">
        @foreach($recent_orders as $order)
        @php
        $statusLabels = [
            'pending_payment' => 'Menunggu Bayar',
            'paid' => 'Dibayar',
            'packed' => 'Dikemas',
            'shipped' => 'Dikirim',
            'arrived' => 'Tiba',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
        $badgeClass = match($order->status) {
            'completed', 'paid' => 'badge-success-custom',
            'pending_payment', 'packed', 'shipped' => 'badge-warning-custom',
            default => 'badge-danger-custom'
        };
        @endphp
        <div class="mobile-dash-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <span style="font-family: monospace; font-weight: 800; color: #0B023E; font-size: 13.5px;">
                    #{{ $order->id }}
                </span>
                <span class="badge-type {{ $badgeClass }}">
                    {{ $statusLabels[$order->status] ?? ucfirst($order->status) }}
                </span>
            </div>
            <div style="font-weight: 700; font-size: 13px; color: #0f172a; margin-bottom: 6px;">
                👤 {{ $order->user?->name ?? '-' }}
            </div>
            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b;">
                <span style="font-weight: 800; color: #0f172a; font-size: 13px;">
                    Rp {{ number_format($order->grand_total, 0, ',', '.') }}
                </span>
                <span>📅 {{ $order->created_at->format('d M Y') }}</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
    .desktop-table-container { display: block; }
    .mobile-dashboard-grid { display: none; padding: 14px; gap: 12px; flex-direction: column; }
    .mobile-dash-card {
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 12px;
        padding: 14px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
    }

    @media (max-width: 768px) {
        .dashboard-grid { grid-template-columns: 1fr !important; }
        .desktop-table-container { display: none !important; }
        .mobile-dashboard-grid { display: flex !important; }
    }
</style>
@endsection