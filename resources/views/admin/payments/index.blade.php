@extends('admin.layouts.app')
@section('title','Kelola Pembayaran')
@section('page-title','Pembayaran')
@section('breadcrumb')<span class="breadcrumb-sep">›</span><span>Pembayaran</span>@endsection
@section('content')

@php
$statusColors = ['waiting_payment'=>'badge-warning','paid'=>'badge-active','expired'=>'badge-inactive','failed'=>'badge-failed'];
$statusLabels = ['waiting_payment'=>'Menunggu Bayar','paid'=>'Dibayar','expired'=>'Kedaluwarsa','failed'=>'Gagal'];
$bankLabels   = ['bca'=>'BCA','bni'=>'BNI','bri'=>'BRI','mandiri'=>'Mandiri','permata'=>'Permata'];
@endphp

<div class="card">
    <div class="card-header">
        <span class="card-title">💳 Daftar Pembayaran</span>
    </div>

    <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
        <form method="GET" action="{{ route('admin.payments.index') }}" class="filter-bar">
            <input type="text" name="search" class="form-control search-input"
                placeholder="🔍 Cari VA, invoice, nama..." value="{{ request('search') }}">
            <select name="status" class="form-control">
                <option value="">Semua Status</option>
                @foreach($statusLabels as $v => $l)
                    <option value="{{ $v }}" {{ request('status')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <select name="bank" class="form-control">
                <option value="">Semua Bank</option>
                @foreach($bankLabels as $v => $l)
                    <option value="{{ $v }}" {{ request('bank')===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['search','status','bank']))
                <a href="{{ route('admin.payments.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrapper">
        @if($payments->isEmpty())
            <div class="empty-state"><div class="empty-state-icon">💳</div><h3>Belum ada pembayaran</h3></div>
        @else
        <table>
            <thead>
                <tr><th>VA Number</th><th>Invoice</th><th>Customer</th><th>Bank</th><th>Jumlah</th><th>Status</th><th>Dibayar</th><th>Kedaluwarsa</th><th>Aksi</th></tr>
            </thead>
            <tbody>
            @foreach($payments as $pay)
            <tr>
                <td><code style="font-size:12px;color:var(--info);">{{ $pay->virtual_account_number }}</code></td>
                <td>
                    <a href="{{ route('admin.orders.show', $pay->order) }}"
                       style="font-family:monospace;font-size:12px;color:var(--accent);text-decoration:none;">
                        {{ $pay->order?->invoice_number }}
                    </a>
                </td>
                <td>
                    <div style="font-size:13px;font-weight:500;color:var(--text-primary);">{{ $pay->order?->user?->name }}</div>
                    <div style="font-size:11px;color:var(--text-muted);">{{ $pay->order?->user?->email }}</div>
                </td>
                <td>
                    <span class="badge badge-purple">{{ $bankLabels[$pay->bank_code] ?? strtoupper($pay->bank_code) }}</span>
                </td>
                <td style="font-weight:600;color:var(--text-primary);">Rp {{ number_format($pay->amount,0,',','.') }}</td>
                <td><span class="badge {{ $statusColors[$pay->status]??'badge-inactive' }}">{{ $statusLabels[$pay->status]??$pay->status }}</span></td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $pay->paid_at?->format('d M Y H:i') ?? '—' }}</td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $pay->expired_at?->format('d M Y H:i') ?? '—' }}</td>
                <td>
                    <a href="{{ route('admin.payments.show', $pay) }}" class="btn btn-info btn-sm btn-icon" title="Detail">
                        <iconify-icon icon="flat-color-icons:view-details" style="font-size: 16px;"></iconify-icon>
                    </a>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @if($payments->hasPages())
    <div class="pagination-wrap">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <span style="font-size:13px;color:var(--text-muted);">{{ $payments->firstItem() }}–{{ $payments->lastItem() }} dari {{ $payments->total() }}</span>
            {{ $payments->links('admin.partials.pagination') }}
        </div>
    </div>
    @endif
</div>
@endsection
