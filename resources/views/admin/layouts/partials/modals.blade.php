<!-- Confirm Delete Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-box">
        <div class="modal-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2" style="vertical-align: middle; margin-right: 6px;"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
            Konfirmasi Hapus
        </div>
        <div class="modal-body" id="confirmModalBody">Apakah Anda yakin ingin menghapus item ini?</div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeConfirm()">Batal</button>
            <form id="confirmForm" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger">Ya, Hapus</button>
            </form>
        </div>
    </div>
</div>

<!-- Confirm Logout Modal -->
<div class="modal-overlay" id="logoutModal">
    <div class="modal-box">
        <div class="modal-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-log-out" style="vertical-align: middle; margin-right: 6px;"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
            Konfirmasi Keluar
        </div>
        <div class="modal-body">Apakah Anda yakin ingin keluar dari Admin Panel?</div>
        <div class="modal-actions">
            <button class="btn btn-secondary" onclick="closeLogoutModal()">Batal</button>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger">Ya, Keluar</button>
            </form>
        </div>
    </div>
</div>
