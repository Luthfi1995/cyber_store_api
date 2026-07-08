@php
    $hasAlerts = session('success') || session('error') || session('warning') || session('info') || $errors->any();
@endphp

@if ($hasAlerts)
<div class="toast-container" id="toastContainer">

    @if (session('success'))
    <div class="toast toast-success" role="alert">
        <div class="toast-icon"><i class="bi bi-check-circle-fill"></i></div>
        <div class="toast-body">
            <div class="toast-title">Berhasil</div>
            <div class="toast-message">{{ session('success') }}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast'))"><i class="bi bi-x-lg"></i></button>
        <div class="toast-progress success-progress"></div>
    </div>
    @endif

    @if (session('error'))
    <div class="toast toast-danger" role="alert">
        <div class="toast-icon"><i class="bi bi-exclamation-circle-fill"></i></div>
        <div class="toast-body">
            <div class="toast-title">Error</div>
            <div class="toast-message">{{ session('error') }}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast'))"><i class="bi bi-x-lg"></i></button>
        <div class="toast-progress danger-progress"></div>
    </div>
    @endif

    @if (session('warning'))
    <div class="toast toast-warning" role="alert">
        <div class="toast-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div class="toast-body">
            <div class="toast-title">Peringatan</div>
            <div class="toast-message">{{ session('warning') }}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast'))"><i class="bi bi-x-lg"></i></button>
        <div class="toast-progress warning-progress"></div>
    </div>
    @endif

    @if (session('info'))
    <div class="toast toast-info" role="alert">
        <div class="toast-icon"><i class="bi bi-info-circle-fill"></i></div>
        <div class="toast-body">
            <div class="toast-title">Informasi</div>
            <div class="toast-message">{{ session('info') }}</div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast'))"><i class="bi bi-x-lg"></i></button>
        <div class="toast-progress info-progress"></div>
    </div>
    @endif

    @if ($errors->any())
    <div class="toast toast-danger" role="alert">
        <div class="toast-icon"><i class="bi bi-shield-exclamation"></i></div>
        <div class="toast-body">
            <div class="toast-title">Validasi Gagal</div>
            <div class="toast-message">
                <ul style="margin:4px 0 0; padding-left:16px; line-height:1.7;">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button class="toast-close" onclick="dismissToast(this.closest('.toast'))"><i class="bi bi-x-lg"></i></button>
        <div class="toast-progress danger-progress"></div>
    </div>
    @endif

</div>

<style>
/* ─── Toast Container ─────────────────────────────────────── */
.toast-container {
    position: fixed;
    top: 24px;
    right: 24px;
    z-index: 99999;
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-width: 400px;
    width: 100%;
    pointer-events: none;
}

/* ─── Toast Base ─────────────────────────────────────────── */
.toast {
    pointer-events: auto;
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 16px 16px 20px;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 32px rgba(0,0,0,.12), 0 2px 8px rgba(0,0,0,.08);
    animation: toastSlideIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
    transition: opacity .3s, transform .3s;
    backdrop-filter: blur(12px);
    background: rgba(255,255,255,0.97);
    border: 1px solid rgba(0,0,0,.06);
}

[data-theme="dark"] .toast {
    background: rgba(15, 23, 42, 0.97);
    border-color: rgba(255,255,255,.08);
    box-shadow: 0 8px 32px rgba(0,0,0,.4);
}

.toast.dismissing {
    animation: toastSlideOut .35s ease forwards;
}

/* ─── Toast Types ─────────────────────────────────────────── */
.toast-success { border-left: 4px solid #10B981; }
.toast-danger  { border-left: 4px solid #EF4444; }
.toast-warning { border-left: 4px solid #F59E0B; }
.toast-info    { border-left: 4px solid #3B82F6; }

/* ─── Toast Icon ─────────────────────────────────────────── */
.toast-icon {
    font-size: 22px;
    flex-shrink: 0;
    margin-top: 1px;
}
.toast-success .toast-icon { color: #10B981; }
.toast-danger  .toast-icon { color: #EF4444; }
.toast-warning .toast-icon { color: #F59E0B; }
.toast-info    .toast-icon { color: #3B82F6; }

/* ─── Toast Body ─────────────────────────────────────────── */
.toast-body { flex: 1; min-width: 0; }

.toast-title {
    font-weight: 700;
    font-size: 13px;
    letter-spacing: .3px;
    color: #0F172A;
    margin-bottom: 3px;
}
[data-theme="dark"] .toast-title { color: #F1F5F9; }

.toast-message {
    font-size: 13px;
    color: #475569;
    line-height: 1.45;
    word-break: break-word;
}
[data-theme="dark"] .toast-message { color: #94A3B8; }

/* ─── Toast Close ─────────────────────────────────────────── */
.toast-close {
    flex-shrink: 0;
    background: none;
    border: none;
    cursor: pointer;
    color: #94A3B8;
    font-size: 13px;
    padding: 2px;
    line-height: 1;
    transition: color .2s;
    margin-top: 1px;
}
.toast-close:hover { color: #475569; }
[data-theme="dark"] .toast-close:hover { color: #CBD5E1; }

/* ─── Progress Bar ─────────────────────────────────────────── */
.toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 3px;
    border-radius: 0 0 14px 14px;
    width: 100%;
    transform-origin: left;
    animation: toastProgress 5s linear forwards;
}
.success-progress { background: #10B981; }
.danger-progress  { background: #EF4444; }
.warning-progress { background: #F59E0B; }
.info-progress    { background: #3B82F6; }

/* ─── Keyframes ─────────────────────────────────────────── */
@keyframes toastSlideIn {
    from { opacity: 0; transform: translateX(110%); }
    to   { opacity: 1; transform: translateX(0); }
}
@keyframes toastSlideOut {
    to   { opacity: 0; transform: translateX(120%); }
}
@keyframes toastProgress {
    from { transform: scaleX(1); }
    to   { transform: scaleX(0); }
}
</style>

<script>
function dismissToast(el) {
    if (!el) return;
    el.classList.add('dismissing');
    setTimeout(() => el.remove(), 350);
}

document.querySelectorAll('.toast').forEach(toast => {
    setTimeout(() => dismissToast(toast), 5000);
});
</script>
@endif
