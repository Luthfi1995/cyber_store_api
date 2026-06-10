@extends('admin.layouts.app')
@section('title','Kelola Pesanan')
@section('page-title','Pesanan')
@section('breadcrumb')<span class="breadcrumb-sep">›</span><span>Pesanan</span>@endsection
@section('content')

@php
$statusColors = [
    'pending_payment' => 'badge-warning',
    'paid'            => 'badge-paid',
    'packed'          => 'badge-info',
    'shipped'         => 'badge-purple',
    'arrived'         => 'badge-recommended',
    'completed'       => 'badge-active',
    'cancelled'       => 'badge-inactive',
];
@endphp

<div class="card">
    <div class="card-header">
        <span class="card-title">🛒 Daftar Pesanan</span>
        <span style="font-size:13px;color:var(--text-muted);">Total: {{ $orders->total() }} order</span>
    </div>

    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="filter-bar">
            <input type="text" name="search" class="form-control search-input"
                placeholder="🔍 Cari invoice, nama customer..." value="{{ request('search') }}">
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                @foreach($statusLabels as $val => $label)
                    <option value="{{ $val }}" {{ request('status')===$val?'selected':'' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['search','status']))
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrapper">
        @if($orders->isEmpty())
            <div class="empty-state"><div class="empty-state-icon">🛒</div><h3>Belum ada pesanan</h3></div>
        @else
        <table>
            <thead>
                <tr><th>Invoice</th><th>Customer</th><th>Ekspedisi</th><th>Subtotal</th><th>Ongkir</th><th>Grand Total</th><th>Status</th><th>Resi</th><th>Tanggal</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            @foreach($orders as $order)
            <tr>
                <td>
                    <a href="{{ route('admin.orders.show',$order) }}"
                       style="font-family:monospace;color:var(--accent);font-weight:600;text-decoration:none;">
                        {{ $order->invoice_number }}
                    </a>
                </td>
                <td>
                    <div style="font-weight:500;color:var(--text-primary);">{{ $order->user?->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $order->user?->email }}</div>
                </td>
                <td style="font-size:12px;">{{ $order->expedition?->name }}</td>
                <td>Rp {{ number_format($order->subtotal,0,',','.') }}</td>
                <td>Rp {{ number_format($order->shipping_cost,0,',','.') }}</td>
                <td style="font-weight:700;color:var(--text-primary);">Rp {{ number_format($order->grand_total,0,',','.') }}</td>
                <td><span class="badge {{ $statusColors[$order->status] ?? 'badge-inactive' }}">{{ $statusLabels[$order->status] ?? $order->status }}</span></td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $order->resi_number ?? '—' }}</td>
                <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;">{{ $order->created_at->format('d M Y') }}</td>
                <td>
                    <a href="{{ route('admin.orders.show',$order) }}" class="btn btn-info btn-sm btn-icon" title="Detail">👁️</a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @if($orders->hasPages())
    <div class="pagination-wrap">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <span style="font-size:13px;color:var(--text-muted);">{{ $orders->firstItem() }}–{{ $orders->lastItem() }} dari {{ $orders->total() }}</span>
            {{ $orders->links('admin.partials.pagination') }}
        </div>
    </div>
    @endif
</div>
@endsection
