@extends('admin.layouts.app')
@section('title','Mutasi Stok')
@section('page-title','Mutasi Stok')
@section('breadcrumb')<span class="breadcrumb-sep">›</span><span>Mutasi Stok</span>@endsection
@section('content')

<style>
    .desktop-table-container { display: block; }
    .mobile-movement-grid { display: none; padding: 16px; gap: 14px; flex-direction: column; }
    .mobile-movement-card {
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    @media (max-width: 768px) {
        .stock-grid { grid-template-columns: 1fr !important; }
        .desktop-table-container { display: none !important; }
        .mobile-movement-grid { display: flex !important; }
    }
</style>

<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;" class="stock-grid">

    {{-- Tabel Mutasi --}}
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <span class="card-title">📈 Riwayat Mutasi Stok</span>
            <a href="{{ route('admin.stock-movements.pdf', request()->query()) }}" class="btn btn-secondary" style="font-size:12px;padding:6px 12px;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                📄 Download PDF
            </a>
        </div>

        <div style="padding:14px 20px;border-bottom:1px solid var(--border);">
            <form method="GET" action="{{ route('admin.stock-movements.index') }}" class="filter-bar">
                <input type="text" name="search" class="form-control search-input"
                    placeholder="🔍 Cari produk, referensi..." value="{{ request('search') }}">
                <select name="product_id" class="form-control">
                    <option value="">Semua Produk</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ request('product_id')==$p->id?'selected':'' }}>
                            {{ \Illuminate\Support\Str::limit($p->name, 35) }}
                        </option>
                    @endforeach
                </select>
                <select name="type" class="form-control">
                    <option value="">Semua Tipe</option>
                    <option value="in"  {{ request('type')==='in' ?'selected':'' }}>📥 Masuk</option>
                    <option value="out" {{ request('type')==='out'?'selected':'' }}>📤 Keluar</option>
                </select>
                <button type="submit" class="btn btn-primary">Filter</button>
                @if(request()->hasAny(['search','type','product_id']))
                    <a href="{{ route('admin.stock-movements.index') }}" class="btn btn-secondary">Reset</a>
                @endif
            </form>
        </div>

        @if($movements->isEmpty())
            <div class="empty-state"><div class="empty-state-icon">📈</div><h3>Belum ada mutasi stok</h3></div>
        @else
            <!-- Desktop Table View (>768px) -->
            <div class="table-wrapper desktop-table-container">
                <table>
                    <thead>
                        <tr><th>Produk</th><th>Tipe</th><th>Qty</th><th>Referensi</th><th>Catatan</th><th>Oleh</th><th>Tanggal</th></tr>
                    </thead>
                    <tbody>
                    @foreach($movements as $mov)
                    <tr>
                        <td>
                            <div style="font-weight:500;color:var(--text-primary);">{{ \Illuminate\Support\Str::limit($mov->product?->name,30) ?? '—' }}</div>
                            @if($mov->product)
                                <div style="font-size:11px;color:var(--text-muted);">Stok kini: <strong>{{ $mov->product->stock }}</strong></div>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $mov->type==='in'?'badge-in':'badge-out' }}">
                                {{ $mov->type==='in'?'📥 Masuk':'📤 Keluar' }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size:15px;font-weight:700;" class="{{ $mov->type==='in'?'text-success':'text-danger' }}">
                                {{ $mov->type==='in'?'+':'-' }}{{ $mov->quantity }}
                            </span>
                        </td>
                        <td style="font-size:12px;color:var(--text-muted);">{{ $mov->reference ?? '—' }}</td>
                        <td style="font-size:12px;color:var(--text-muted);max-width:150px;">{{ \Illuminate\Support\Str::limit($mov->note,40) ?? '—' }}</td>
                        <td style="font-size:12px;color:var(--text-muted);">{{ $mov->user?->name ?? 'Sistem' }}</td>
                        <td style="font-size:12px;color:var(--text-muted);white-space:nowrap;">{{ $mov->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Mobile Movement Card View (<=768px) -->
            <div class="mobile-movement-grid">
                @foreach($movements as $mov)
                <div class="mobile-movement-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <div style="font-weight: 800; font-size: 14px; color: var(--text-primary);">
                            📦 {{ \Illuminate\Support\Str::limit($mov->product?->name, 35) ?? '—' }}
                        </div>
                        <span class="badge {{ $mov->type==='in'?'badge-in':'badge-out' }}">
                            {{ $mov->type==='in'?'📥 Masuk':'📤 Keluar' }}
                        </span>
                    </div>

                    <div style="background: var(--bg-input, #f8fafc); padding: 10px 12px; border-radius: 8px; font-size: 12.5px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                        <div>Jumlah: <span style="font-size: 15px; font-weight: 800;" class="{{ $mov->type==='in'?'text-success':'text-danger' }}">{{ $mov->type==='in'?'+':'-' }}{{ $mov->quantity }}</span> Pcs</div>
                        <div>Stok Saat Ini: <strong>{{ $mov->product?->stock ?? 0 }}</strong></div>
                    </div>

                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 6px;">
                        📝 Ref: {{ $mov->reference ?? '—' }}
                    </div>
                    @if($mov->note)
                    <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 8px;">
                        💬 {{ $mov->note }}
                    </div>
                    @endif
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 11px; color: var(--text-muted); border-top: 1px solid var(--border); padding-top: 8px; margin-top: 8px;">
                        <span>👤 Oleh: {{ $mov->user?->name ?? 'Sistem' }}</span>
                        <span>📅 {{ $mov->created_at->format('d M Y, H:i') }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        @endif

        @if($movements->hasPages())
        <div class="pagination-wrap">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <span style="font-size:13px;color:var(--text-muted);">{{ $movements->firstItem() }}–{{ $movements->lastItem() }} dari {{ $movements->total() }}</span>
                {{ $movements->links('admin.partials.pagination') }}
            </div>
        </div>
        @endif
    </div>

    {{-- Form Input Manual --}}
    <div class="card" style="height:fit-content;">
        <div class="card-header"><span class="card-title">➕ Input Mutasi Manual</span></div>
        <div class="card-body">
            <form id="stockMutationForm" method="POST" action="{{ route('admin.stock-movements.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="product_id">Produk <span style="color:var(--danger)">*</span></label>
                    <select id="product_id" name="product_id" class="form-control" required>
                        <option value="">— Pilih Produk —</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ old('product_id')==$p->id?'selected':'' }}>
                                {{ Str::limit($p->name,35) }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="type">Tipe Mutasi <span style="color:var(--danger)">*</span></label>
                    <select id="type" name="type" class="form-control" required>
                        <option value="">— Pilih Tipe —</option>
                        <option value="in"  {{ old('type')==='in' ?'selected':'' }}>📥 Masuk (Tambah Stok)</option>
                        <option value="out" {{ old('type')==='out'?'selected':'' }}>📤 Keluar (Kurangi Stok)</option>
                    </select>
                    @error('type')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="quantity">Jumlah <span style="color:var(--danger)">*</span></label>
                    <input type="number" id="quantity" name="quantity" class="form-control"
                        value="{{ old('quantity',1) }}" min="1" required>
                    @error('quantity')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="reference">Referensi</label>
                    <input type="text" id="reference" name="reference" class="form-control"
                        value="{{ old('reference') }}" placeholder="No. PO, Kode Retur, dll">
                    @error('reference')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="note">Catatan</label>
                    <textarea id="note" name="note" class="form-control" rows="2"
                        placeholder="Keterangan tambahan...">{{ old('note') }}</textarea>
                    @error('note')<div class="form-error">{{ $message }}</div>@enderror
                </div>

                <button type="button" class="btn btn-primary" style="width:100%;" onclick="confirmCreate('stockMutationForm', 'Konfirmasi Catat Mutasi', 'Apakah Anda yakin ingin mencatat mutasi stok ini?')">📊 Catat Mutasi</button>
            </form>
        </div>
    </div>
</div>

<style>
@media (max-width: 900px) { .stock-grid { grid-template-columns: 1fr !important; } }
</style>
@endsection
