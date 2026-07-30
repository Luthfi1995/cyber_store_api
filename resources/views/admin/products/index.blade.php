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
        <div style="display: flex; gap: 8px; align-items: center;">
            <button type="button" id="btn-bulk-delete" class="btn btn-danger" style="display: none; align-items: center; gap: 6px; background-color: #EF4444; border-color: #EF4444; color: white;" onclick="confirmBulkDelete()">
                🗑️ Hapus Terpilih (<span id="selected-count">0</span>)
            </button>
            {{-- Tombol flush cache produk di Redis agar Flutter langsung segar --}}
            <form method="POST" action="{{ route('admin.cache.flush-products') }}" style="margin: 0;" onsubmit="return confirm('Bersihkan cache produk di server? Flutter akan langsung menampilkan data terbaru.')">
                @csrf
                <button type="submit" class="btn btn-secondary" style="display: inline-flex; align-items: center; gap: 6px; background-color: #F59E0B; border-color: #F59E0B; color: white;">
                    🔄 Bersihkan Cache
                </button>
            </form>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('import-section').style.display = document.getElementById('import-section').style.display === 'none' ? 'block' : 'none'" style="display: inline-flex; align-items: center; gap: 6px;">
                📥 Impor Massal (CSV)
            </button>
            <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
                ＋ Tambah Produk
            </a>
        </div>
    </div>


    <style>
    .desktop-table-container { display: block; }
    .mobile-product-grid { display: none; padding: 16px; gap: 14px; flex-direction: column; }
    .mobile-product-card {
        background: var(--bg-card, #ffffff);
        border: 1px solid var(--border, #e2e8f0);
        border-radius: 14px;
        padding: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }
    @media (max-width: 768px) {
        .desktop-table-container { display: none !important; }
        .mobile-product-grid { display: flex !important; }
    }
    @keyframes spinCustom {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    .spinner-border-custom {
        display: inline-block;
        width: 14px;
        height: 14px;
        border: 2px solid #ffffff;
        border-right-color: transparent;
        border-radius: 50%;
        animation: spinCustom 0.75s linear infinite;
        vertical-align: middle;
    }
    </style>

    @if(session('import_errors'))
    <div id="import-section" style="padding: 20px; background: #f8fafc; border-bottom: 1px solid var(--border);">
    @else
    <div id="import-section" style="display: none; padding: 20px; background: #f8fafc; border-bottom: 1px solid var(--border);">
    @endif
        <form action="{{ route('admin.products.import') }}" method="POST" enctype="multipart/form-data" id="importCsvForm" onsubmit="return handleCsvImportSubmit(this)" class="filter-bar" style="align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            @csrf
            <div style="display: flex; flex-direction: column; gap: 4px;">
                <label style="font-weight: bold; font-size: 13px;">Pilih File (.csv / .xls / .xlsx)</label>
                <input type="file" name="file" accept=".csv,.xls,.xlsx" required class="form-control" style="background: white;" id="csvFileInput">
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <button type="submit" id="importSubmitBtn" class="btn btn-success" style="background-color: #10B981; border-color: #10B981; color: white; display: inline-flex; align-items: center; gap: 8px;">
                    <span id="importBtnIcon"><iconify-icon icon="flat-color-icons:upload" style="font-size: 18px;"></iconify-icon></span> <span id="importBtnText">Proses Impor</span>
                </button>
                <a href="{{ route('admin.products.import-template') }}" class="btn btn-link" style="font-size: 13px; text-decoration: underline; color: #0D47A1; font-weight: bold; display: inline-flex; align-items: center; gap: 6px;">
                    <iconify-icon icon="vscode-icons:file-type-excel" style="font-size: 18px;"></iconify-icon> Unduh Template Excel (.xls)
                </a>
            </div>
        </form>

        <div id="import-status-box" style="display: none; align-items: center; gap: 12px; margin-top: 14px; padding: 12px 16px; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 8px; color: #1E40AF; font-size: 13px;">
            <span class="spinner-border-custom" style="border-color: #1E40AF; border-right-color: transparent; width: 18px; height: 18px; border-width: 3px;"></span>
            <div>
                <strong>Sedang Memproses & Mengimpor File CSV...</strong><br>
                <span style="font-size: 11px; opacity: 0.85;">Mohon jangan memuat ulang atau menutup halaman sampai proses selesai.</span>
            </div>
        </div>

        <div style="margin-top: 12px; font-size: 11px; color: #64748b; line-height: 1.6;">
            * <strong>Langkah Penggunaan:</strong> <br>
            1. Klik <strong>"Unduh Template Excel (.xls)"</strong> di atas. File ini sudah terformat rapi dengan garis tabel dan warna header.<br>
            2. Buka dan isi data produk Anda menggunakan Microsoft Excel atau Google Sheets.<br>
            3. Setelah selesai mengisi, pilih <strong>File -> Save As (Simpan Sebagai)</strong> lalu pilih format <strong>CSV (Comma Delimited) (*.csv)</strong>.<br>
            4. Upload file <strong>.csv</strong> hasil ekspor tersebut di sini, lalu klik <strong>"Proses Impor"</strong>.<br>
            * <em>Catatan: Pisahkan beberapa ukuran/warna dengan tanda titik koma (<code>;</code>) pada sel yang sama.</em>
        </div>
    </div>

    @if (session('import_errors'))
    <div style="margin: 15px 20px; padding: 15px; background-color: #FEF2F2; border: 1px solid #FEE2E2; border-radius: 8px; position: relative;">
        <button onclick="this.parentElement.remove()" style="position: absolute; top: 12px; right: 12px; background: none; border: none; font-size: 16px; cursor: pointer; color: #991B1B;">✕</button>
        <h4 style="color: #991B1B; margin-top: 0; margin-bottom: 8px; font-size: 13px; font-weight: bold; display: flex; align-items: center; gap: 6px;">
            ⚠️ Beberapa baris memiliki kesalahan data dan dilewati:
        </h4>
        <ul style="color: #B91C1C; margin: 0; padding-left: 20px; font-size: 12px; line-height: 1.6;">
            @foreach (session('import_errors') as $err)
            <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <script>
    function handleCsvImportSubmit(form) {
        const fileInput = document.getElementById('csvFileInput');
        if (!fileInput || !fileInput.files || !fileInput.files.length) {
            alert('Silakan pilih file CSV terlebih dahulu.');
            return false;
        }

        const btn = document.getElementById('importSubmitBtn');
        const icon = document.getElementById('importBtnIcon');
        const text = document.getElementById('importBtnText');
        const statusBox = document.getElementById('import-status-box');

        if (btn) {
            btn.disabled = true;
            btn.style.opacity = '0.85';
            btn.style.cursor = 'not-allowed';
        }
        if (icon) {
            icon.className = 'spinner-border-custom';
            icon.innerHTML = '';
        }
        if (text) {
            text.innerText = 'Memproses Impor...';
        }
        if (statusBox) {
            statusBox.style.display = 'flex';
        }

        return true;
    }
    </script>

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
        <!-- Desktop Table View (>768px) -->
        <div class="table-wrapper desktop-table-container">
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;"><input type="checkbox" id="check-all" style="cursor: pointer; width: 16px; height: 16px;"></th>
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
                        <td style="text-align: center;"><input type="checkbox" name="product_ids[]" value="{{ $product->id }}" class="product-checkbox" style="cursor: pointer; width: 16px; height: 16px;"></td>
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
                            <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}" style="display: inline-flex; align-items: center; gap: 4px;">
                                <iconify-icon icon="{{ $product->is_active ? 'flat-color-icons:checkmark' : 'flat-color-icons:cancel' }}" style="font-size: 13px;"></iconify-icon>
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td>
                            <div class="actions">
                                <a href="{{ route('admin.products.edit', $product) }}"
                                    class="btn btn-secondary btn-sm btn-icon" title="Edit">
                                    <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                                </a>

                                <form method="POST" action="{{ route('admin.products.toggle', $product) }}"
                                    style="display:inline;">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                        class="btn btn-sm btn-icon {{ $product->is_active ? 'btn-warning' : 'btn-success' }}"
                                        title="{{ $product->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                        <iconify-icon icon="{{ $product->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                                    </button>
                                </form>

                                <button type="button" class="btn btn-danger btn-sm btn-icon" title="Hapus"
                                    data-url="{{ route('admin.products.destroy', $product) }}"
                                    data-name="{{ $product->name }}"
                                    onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                                    <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Mobile Product Card View (<=768px) -->
        <div class="mobile-product-grid">
            @foreach ($products as $product)
            <div class="mobile-product-card">
                <div style="display: flex; gap: 12px; align-items: center; margin-bottom: 12px;">
                    <div style="width: 50px; height: 50px; background: var(--bg-input); border: 1px solid var(--border); border-radius: 10px; overflow: hidden; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                        @if ($product->main_photo)
                            <img src="{{ Storage::disk('public')->url($product->main_photo) }}" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <span style="font-size: 24px;">📦</span>
                        @endif
                    </div>
                    <div style="flex: 1;">
                        <div style="font-weight: 800; font-size: 14px; color: var(--text-primary);">
                            {{ $product->name }}
                        </div>
                        <div style="font-size: 11px; color: var(--text-muted); font-family: monospace;">
                            SKU: {{ $product->sku }}
                        </div>
                    </div>
                    <span class="badge {{ $product->is_active ? 'badge-active' : 'badge-inactive' }}">
                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>

                <div style="background: var(--bg-input, #f8fafc); padding: 10px 12px; border-radius: 10px; font-size: 12px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span style="font-weight: 800; color: var(--accent); font-size: 14px;">
                            Rp {{ number_format($product->price, 0, ',', '.') }}
                        </span>
                    </div>
                    <div>
                        Stok: <span style="font-weight: 800;" class="{{ $product->stock <= 5 ? 'text-danger' : 'text-success' }}">{{ $product->stock }} Pcs</span>
                    </div>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; padding: 2px 8px; background: var(--accent-light); color: var(--accent); border-radius: 12px;">
                        {{ $product->category?->name ?? '—' }}
                    </span>
                    <div class="actions" style="gap: 6px;">
                        <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                            <iconify-icon icon="flat-color-icons:edit-image" style="font-size: 16px;"></iconify-icon>
                        </a>
                        <form method="POST" action="{{ route('admin.products.toggle', $product) }}" style="display:inline;">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-sm btn-icon {{ $product->is_active ? 'btn-warning' : 'btn-success' }}">
                                <iconify-icon icon="{{ $product->is_active ? 'flat-color-icons:cancel' : 'flat-color-icons:ok' }}" style="font-size: 16px;"></iconify-icon>
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger btn-sm btn-icon" data-url="{{ route('admin.products.destroy', $product) }}" data-name="{{ $product->name }}" onclick="confirmDelete(this.dataset.url, this.dataset.name)">
                            <iconify-icon icon="fluent-emoji-flat:wastebasket" style="font-size: 16px;"></iconify-icon>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
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

<!-- Modal: Konfirmasi Hapus Massal -->
<div class="modal-overlay" id="bulkConfirmModal">
    <div class="modal-box">
        <div class="modal-icon-wrap danger-icon">
            <i class="bi bi-trash3-fill"></i>
        </div>
        <div class="modal-title">Konfirmasi Hapus Massal</div>
        <div class="modal-body" id="bulkConfirmModalBody">Apakah Anda yakin ingin menghapus produk yang dipilih? Tindakan ini tidak dapat dibatalkan.</div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary" onclick="_closeModal('bulkConfirmModal')">
                <i class="bi bi-x-lg"></i> Batal
            </button>
            <form id="bulk-delete-form" action="{{ route('admin.products.bulk-destroy') }}" method="POST">
                @csrf
                @method('DELETE')
                <div id="bulk-delete-ids"></div>
                <button type="submit" class="btn btn-danger" onclick="_closeModal('bulkConfirmModal'); showLoading('Menghapus produk terpilih…')">
                    <i class="bi bi-trash3"></i> Ya, Hapus Semua
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkAll = document.getElementById('check-all');
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const btnBulkDelete = document.getElementById('btn-bulk-delete');
        const selectedCount = document.getElementById('selected-count');

        function updateBulkDeleteButton() {
            const checkedCount = document.querySelectorAll('.product-checkbox:checked').length;
            if (checkedCount > 0) {
                btnBulkDelete.style.display = 'inline-flex';
                selectedCount.textContent = checkedCount;
            } else {
                btnBulkDelete.style.display = 'none';
            }
        }

        if (checkAll) {
            checkAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    cb.checked = checkAll.checked;
                });
                updateBulkDeleteButton();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                // If any is unchecked, checkAll should be unchecked
                if (!this.checked) {
                    if (checkAll) checkAll.checked = false;
                } else {
                    // Check if all checkboxes are checked
                    const allChecked = Array.from(checkboxes).every(c => c.checked);
                    if (checkAll) checkAll.checked = allChecked;
                }
                updateBulkDeleteButton();
            });
        });

        window.confirmBulkDelete = function() {
            const checkedCheckboxes = document.querySelectorAll('.product-checkbox:checked');
            if (checkedCheckboxes.length === 0) return;

            const bulkConfirmModalBody = document.getElementById('bulkConfirmModalBody');
            if (bulkConfirmModalBody) {
                bulkConfirmModalBody.textContent = `Apakah Anda yakin ingin menghapus ${checkedCheckboxes.length} produk terpilih? Tindakan ini tidak dapat dibatalkan.`;
            }

            const idsContainer = document.getElementById('bulk-delete-ids');
            if (idsContainer) {
                idsContainer.innerHTML = '';
                checkedCheckboxes.forEach(cb => {
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'ids[]';
                    hiddenInput.value = cb.value;
                    idsContainer.appendChild(hiddenInput);
                });
            }

            _openModal('bulkConfirmModal');
        }
    });
</script>
@endpush
@endsection