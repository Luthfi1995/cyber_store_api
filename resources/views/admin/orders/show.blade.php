@extends('admin.layouts.app')
@section('title', 'Detail Pesanan — ' . $order->invoice_number)
@section('page-title', 'Detail Pesanan')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span><a href="{{ route('admin.orders.index') }}">Pesanan</a>
    <span class="breadcrumb-sep">›</span><span>{{ $order->invoice_number }}</span>
@endsection

@section('content')
@php
$statusColors = [
    'pending_payment'=>'badge-warning','paid'=>'badge-paid','packed'=>'badge-info',
    'shipped'=>'badge-purple','arrived'=>'badge-recommended','completed'=>'badge-active','cancelled'=>'badge-inactive',
];
@endphp

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;" class="order-grid">

    {{-- Kiri: Detail --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Info Order --}}
        <div class="card">
            <div class="card-header">
                <span class="card-title">📋 Informasi Pesanan</span>
                <span class="badge {{ $statusColors[$order->status]??'badge-inactive' }}">{{ $statusLabels[$order->status]??$order->status }}</span>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">INVOICE</div>
                        <div style="font-family:monospace;font-size:15px;font-weight:700;color:var(--accent);">{{ $order->invoice_number }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">TANGGAL</div>
                        <div style="font-weight:500;">{{ $order->created_at->format('d M Y, H:i') }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">EKSPEDISI</div>
                        <div style="font-weight:500;">{{ $order->expedition?->name }}</div>
                    </div>
                    <div>
                        <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">NOMOR RESI</div>
                        <div style="font-weight:500;">{{ $order->resi_number ?? '—' }}</div>
                    </div>
                </div>

                @if($order->note)
                <div style="margin-top:16px;padding:12px;background:var(--bg-input);border-radius:var(--radius-sm);border-left:3px solid var(--accent);">
                    <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">CATATAN CUSTOMER</div>
                    <div style="font-size:13px;">{{ $order->note }}</div>
                </div>
                @endif
            </div>
        </div>

        {{-- Item Pesanan --}}
        <div class="card">
            <div class="card-header"><span class="card-title">📦 Item Pesanan ({{ $order->items->count() }})</span></div>
            <div class="table-wrapper">
                <table>
                    <thead><tr><th>Produk</th><th>Varian</th><th>Harga</th><th>Qty</th><th>Total</th></tr></thead>
                    <tbody>
                    @foreach($order->items as $item)
                    <tr>
                        <td>
                            <div style="font-weight:500;color:var(--text-primary);">{{ $item->product_name }}</div>
                            @if($item->product_id && !$item->product)
                                <div style="font-size:11px;color:var(--danger);">(Produk dihapus)</div>
                            @endif
                        </td>
                        <td>
                            <div style="font-size:12px;color:var(--text-muted);">
                                @if($item->size) <span>📏 {{ $item->size }}</span> @endif
                                @if($item->color) <span style="margin-left:6px;">🎨 {{ $item->color }}</span> @endif
                                @if(!$item->size && !$item->color) — @endif
                            </div>
                        </td>
                        <td>Rp {{ number_format($item->price,0,',','.') }}</td>
                        <td style="font-weight:600;color:var(--text-primary);">{{ $item->quantity }}</td>
                        <td style="font-weight:600;color:var(--text-primary);">Rp {{ number_format($item->total,0,',','.') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div style="padding:16px 20px;border-top:1px solid var(--border);">
                <div style="display:flex;flex-direction:column;gap:6px;max-width:260px;margin-left:auto;">
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);">
                        <span>Subtotal</span><span>Rp {{ number_format($order->subtotal,0,',','.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:13px;color:var(--text-muted);">
                        <span>Ongkos Kirim</span><span>Rp {{ number_format($order->shipping_cost,0,',','.') }}</span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:700;color:var(--text-primary);padding-top:8px;border-top:1px solid var(--border);">
                        <span>Grand Total</span><span>Rp {{ number_format($order->grand_total,0,',','.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tracking --}}
        @if($order->trackings->isNotEmpty())
        <div class="card">
            <div class="card-header"><span class="card-title">📍 Riwayat Tracking</span></div>
            <div class="card-body">
                <div style="display:flex;flex-direction:column;gap:0;">
                @foreach($order->trackings as $i => $track)
                <div style="display:flex;gap:14px;">
                    <div style="display:flex;flex-direction:column;align-items:center;">
                        <div style="width:12px;height:12px;border-radius:50%;background:{{ $i===0?'var(--accent)':'var(--border)' }};flex-shrink:0;margin-top:4px;"></div>
                        @if(!$loop->last)
                            <div style="width:2px;flex:1;background:var(--border);margin:4px 0;"></div>
                        @endif
                    </div>
                    <div style="padding-bottom:16px;">
                        <div style="font-size:12px;color:var(--text-muted);">{{ $track->created_at->format('d M Y H:i') }}</div>
                        <div style="font-weight:500;color:var(--text-primary);margin-top:2px;">{{ $track->description }}</div>
                        @if($track->location)
                            <div style="font-size:12px;color:var(--text-muted);margin-top:2px;">📍 {{ $track->location }}</div>
                        @endif
                    </div>
                </div>
                @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- Kanan: Aksi & Info --}}
    <div style="display:flex;flex-direction:column;gap:20px;">

        {{-- Customer --}}
        <div class="card">
            <div class="card-header"><span class="card-title">👤 Customer</span></div>
            <div class="card-body">
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                    <div style="width:44px;height:44px;border-radius:50%;overflow:hidden;flex-shrink:0;">
                        @if($order->user?->photo)
                            <img src="{{ Storage::disk('public')->url($order->user->photo) }}" style="width:100%;height:100%;object-fit:cover;">
                        @else
                            <div style="width:100%;height:100%;background:linear-gradient(135deg,#4f6ef7,#7c3aed);display:flex;align-items:center;justify-content:center;font-weight:700;color:#fff;font-size:18px;">
                                {{ strtoupper(substr($order->user?->name??'?',0,1)) }}
                            </div>
                        @endif
                    </div>
                    <div>
                        <div style="font-weight:600;color:var(--text-primary);">{{ $order->user?->name }}</div>
                        <div style="font-size:12px;color:var(--text-muted);">{{ $order->user?->email }}</div>
                    </div>
                </div>
                @if($order->address)
                <div style="font-size:12px;color:var(--text-muted);line-height:1.6;padding:10px;background:var(--bg-input);border-radius:var(--radius-sm);">
                    <div style="font-weight:600;color:var(--text-secondary);margin-bottom:4px;">📍 {{ $order->address->label }}</div>
                    <div>{{ $order->address->receiver_name }} · {{ $order->address->phone }}</div>
                    <div>{{ $order->address->address }}</div>
                    <div>{{ $order->address->district }}, {{ $order->address->city }}</div>
                    <div>{{ $order->address->province }} {{ $order->address->postal_code }}</div>
                    
                    @if($order->address->latitude && $order->address->longitude)
                    <div style="margin-top: 12px;">
                        <iframe 
                            width="100%" 
                            height="180" 
                            frameborder="0" 
                            style="border:0; border-radius: var(--radius-sm); display: block;" 
                            src="https://maps.google.com/maps?q={{ $order->address->latitude }},{{ $order->address->longitude }}&hl=id&z=15&output=embed" 
                            allowfullscreen>
                        </iframe>
                        <div style="margin-top: 6px; text-align: right;">
                            <a href="https://www.google.com/maps/search/?api=1&query={{ $order->address->latitude }},{{ $order->address->longitude }}" 
                               target="_blank" 
                               style="color: var(--accent); font-size: 11px; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                               🌐 Buka di Google Maps
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Pembayaran --}}
        @if($order->payment)
        <div class="card">
            <div class="card-header"><span class="card-title">💳 Pembayaran</span></div>
            <div class="card-body">
                @php $pBadge = ['waiting_payment'=>'badge-warning','paid'=>'badge-active','expired'=>'badge-inactive','failed'=>'badge-danger']; @endphp
                <span class="badge {{ $pBadge[$order->payment->status]??'badge-inactive' }}" style="margin-bottom:12px;">
                    {{ ucwords(str_replace('_',' ',$order->payment->status)) }}
                </span>
                <div style="font-size:13px;color:var(--text-muted);margin-top:8px;display:flex;flex-direction:column;gap:4px;">
                    <div>🏦 <strong>{{ strtoupper($order->payment->bank_code) }}</strong></div>
                    <div>🔢 {{ $order->payment->virtual_account_number }}</div>
                    <div>💰 Rp {{ number_format($order->payment->amount,0,',','.') }}</div>
                    @if($order->payment->paid_at)
                        <div>✅ Dibayar: {{ $order->payment->paid_at->format('d M Y H:i') }}</div>
                    @endif
                </div>
            </div>
        </div>
        @endif

        {{-- Update Status --}}
        <div class="card">
            <div class="card-header"><span class="card-title">🔄 Ubah Status</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.status',$order) }}">
                    @csrf @method('PATCH')
                    <div class="form-group">
                        <label class="form-label">Status Baru</label>
                        <select name="status" class="form-control" required>
                            @foreach($statusLabels as $val => $label)
                                <option value="{{ $val }}" {{ $order->status===$val?'selected':'' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">💾 Perbarui Status</button>
                </form>
            </div>
        </div>

        {{-- Update Resi --}}
        <div class="card">
            <div class="card-header"><span class="card-title">📦 Nomor Resi</span></div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.orders.resi',$order) }}">
                    @csrf @method('PATCH')
                    <div class="form-group">
                        <label class="form-label">Nomor Resi Pengiriman</label>
                        <input type="text" name="resi_number" class="form-control"
                            value="{{ $order->resi_number }}" placeholder="Masukkan nomor resi">
                    </div>
                    <button type="submit" class="btn btn-success" style="width:100%;">📋 Simpan Resi</button>
                </form>
            </div>
        </div>

        <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary" style="justify-content:center;">← Kembali ke Daftar</a>
    </div>
</div>

<style>
@media (max-width: 900px) { .order-grid { grid-template-columns: 1fr !important; } }
</style>
@endsection
