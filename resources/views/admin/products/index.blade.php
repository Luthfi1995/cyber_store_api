@extends('admin.layouts.app')

@section('title', 'Kelola Produk')
@section('page-title', 'Kelola Produk')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<span>Produk</span>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <span class="card-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-package">
                <path d="m7.5 4.27 9 5.15" />
                <path
                    d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                <path d="m3.27 6.96 8.73 5 8.73-5" />
                <path d="M12 22.08V12" />
            </svg> Daftar Produk</span>
        <div style="display: flex; gap: 8px;">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('import-section').style.display = document.getElementById('import-section').style.display === 'none' ? 'block' : 'none'" style="display: inline-flex; align-items: center; gap: 6px;">
                📥 Impor Massal (CSV)
            </button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                ＋ Tambah Produk
            </a>
        </div>
    </div>

    <div id="import-section" style="display: none; padding: 20px; background: #f8fafc; border-bottom: 1px solid var(--border);">
        <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" class="filter-bar" style="align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label style="font-weight: bold; font-size: 13px;">Pilih File CSV (.csv)</label>
                <input type="file" name="file" accept=".csv" required class="form-control" style="background: white;">
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="submit" class="btn btn-success" style="background-color: #10B981; border-color: #10B981; color: white;">
                    🚀 Proses Impor
                </button>
                <a href="{{ route('admin.products.import-template') }}" class="btn btn-link" style="font-size: 13px; text-decoration: underline; color: #0D47A1; font-weight: bold;">
                    📥 Unduh Template Excel (.xls)
                </a>
            </div>
        </form>
        <div style="margin-top: 10px; font-size: 11px; color: #64748b; line-height: 1.6;">
            * <strong>Langkah Penggunaan:</strong> <br>
            1. Klik <strong>"Unduh Template Excel (.xls)"</strong> di atas. File ini sudah terformat rapi dengan garis tabel dan warna header.<br>
            2. Buka dan isi data produk Anda menggunakan Microsoft Excel atau Google Sheets.<br>
            3. Setelah selesai mengisi, pilih <strong>File -> Save As (Simpan Sebagai)</strong> lalu pilih format <strong>CSV (Comma Delimited) (*.csv)</strong>.<br>
            4. Upload file <strong>.csv</strong> hasil ekspor tersebut di sini, lalu klik <strong>"Proses Impor"</strong>.<br>
            * <em>Catatan: Pisahkan beberapa ukuran/warna dengan tanda titik koma (<code>;</code>) pada sel yang sama.</em>
        </div>
    </div>

    @if (session('import_errors'))
    <div style="margin: 15px 20px; padding: 15px; background-color: #FEF2F2; border: 1px solid #FEE2E2; border-radius: 8px;">
        <h4 style="color: #991B1B; margin-top: 0; margin-bottom: 8px; font-size: 13px; font-weight: bold;">⚠️ Beberapa baris memiliki kesalahan data dan dilewati:</h4>
        <ul style="color: #B91C1C; margin: 0; padding-left: 20px; font-size: 12px; line-height: 1.6;">
            @foreach (session('import_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div style="padding: 16px 20px; border-bottom: 1px solid var(--border);">
        <form method="GET" action="{{ route('admin.products.index') }}" class="filter-bar">
            <div class="search-input-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search search-icon">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-3-3" />
                </svg>
                <input type="text" name="search" class="form-control search-input"
                    placeholder="Cari nama produk, SKU..." value="{{ request('search') }}">
            </div>
            <select name="category" class="form-control" style="min-width:150px;">
                <option value="">Semua Kategori</option>
                @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>
                @endforeach
            </select>
            <select name="status" class="form-control" style="min-width:130px;">
                <option value="">Semua Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
            </select>
            <button type="submit" class="btn btn-primary">Filter</button>
            @if (request()->hasAny(['search', 'category', 'status']))
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Reset</a>
            @endif
        </form>
    </div>

    <div class="table-wrapper">
        @if ($products->isEmpty())
        <div class="empty-state">
            <div class="empty-state-icon">📭</div>
            <h3>Tidak ada produk ditemukan</h3>
            <p>Coba ubah filter atau tambahkan produk baru.</p>
        </div>
        @else
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Produk</th>
                    <th>SKU</th>
                    <th>Kategori</th>
                    <th>Harga</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($products as $product)
                <tr>
                    <td style="color:var(--text-muted); font-size:12px;">
                        {{ $loop->iteration + ($products->firstItem() - 1) }}
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div
                                style="width:44px; height:44px; background:var(--bg-input); border:1px solid var(--border); border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; flex-shrink:0;">
                                @if ($product->main_photo)
                                <img src="{{ Storage::disk('public')->url($product->main_photo) }}"
                                    style="width:100%; height:100%; object-fit:cover;">
                                @else
                                <span style="font-size:20px;">
                                    @if ($product->category?->slug === 'topi')
                                    🧢
                                    @elseif($product->category?->slug === 'baju')
                                    👕
                                    @elseif($product->category?->slug === 'tumbler')
                                    🥤
                                    @else
                                    📦
                                    @endif
                                </span>
                                @endif
                            </div>
                            <div>
                                <div style="font-weight:600; color:var(--text-primary);">
                                    {{ \Illuminate\Support\Str::limit($product->name, 35) }}
                                </div>
                                <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">
                                    ⭐ {{ $product->rating }} · {{ $product->reviews_count }} ulasan
                                    @if ($product->is_recommended)
                                    <span class="badge badge-recommended"
                                        style="margin-left:4px;">Rekomendasi</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td style="font-family:monospace; font-size:12px; color:var(--text-muted);">
                        {{ $product->sku }}
                    </td>
                    <td>
                        <span
                            style="font-size:13px; padding:3px 10px; background:var(--accent-light); color:var(--accent); border-radius:20px; font-weight:500;">
                            {{ $product->category?->name ?? '—' }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:600; color:var(--text-primary);">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </div>
                        @if ($product->original_price)
                        <div style="font-size:11px; color:var(--text-muted); text-decoration:line-through;">
                            Rp {{ number_format($product->original_price, 0, ',', '.') }}
                        </div>
                        @endif
                    </td>
                    <td>
                        <span style="font-weight:600;"
                            class="{{ $product->stock <= 5 ? 'text-danger' : ($product->stock <= 20 ? 'text-warning' : 'text-success') }}">
                            {{ $product->stock }}
                        </span>
                    </td>
                    <td>
                        <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}">
                            {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td>
                        <div class="actions">
                            <a href="{{ route('admin.products.edit', $product) }}"
                                class="btn btn-secondary btn-sm btn-icon" title="Edit">✏️</a>

                            {{-- Toggle Active --}}
                            <form method="POST" action="{{ route('admin.products.toggle', $product) }}"
                                style="display:inline;">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="btn btn-sm btn-icon {{ $product->is_active ? 'btn-warning' : 'btn-success' }}"
                                    title="{{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                    {{ $product->is_active ? '🚫' : '✅' }}
                                </button>
                            </form>

                            <button type="button" class="btn btn-danger btn-sm btn-icon" title="Hapus"
                                data-url="{{ route('admin.products.destroy', $product) }}"
                                data-name="{{ $product->name }}"
                                onclick="confirmDelete(this.dataset.url, this.dataset.name)">🗑️</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    @if ($products->hasPages())
    <div class="pagination-wrap">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap; margin: 0 16px;">
            <span style="font-size:13px; color:var(--text-muted);">
                Menampilkan {{ $products->firstItem() }}–{{ $products->lastItem() }} dari
                {{ $products->total() }} produk
            </span>
            {{ $products->links('admin.partials.pagination') }}
        </div>
    </div>
    @endif
</div>
@endsection