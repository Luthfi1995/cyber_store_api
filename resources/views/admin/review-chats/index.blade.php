@extends('admin.layouts.app')
@section('title', isset($review) ? 'Chat Ulasan - ' . $review->user?->name : 'Chat Ulasan')
@section('page-title', 'Chat Ulasan')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<span>Chat Ulasan</span>
@endsection

@push('styles')
<style>
    /* ── Slack-like Review Workspace (Theme Adaptive) ── */
    .slack-chat-workspace {
        display: flex;
        height: calc(100vh - 120px);
        background: #F1F5F9;
        padding: 16px;
        gap: 16px;
        color: #1e293b;
        font-family: 'Inter', sans-serif;
    }

    /* Left Sidebar */
    .slack-sidebar {
        width: 290px;
        background: #D3D3D3;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .slack-sidebar-header {
        padding: 18px 20px;
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        background: #D3D3D3;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .slack-search-bar {
        padding: 12px;
        border-bottom: 1px solid #f1f5f9;
        background: transparent;
    }

    .slack-search-input {
        width: 100%;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        border-radius: 20px;
        padding: 8px 16px;
        color: #1e293b;
        font-size: 13px;
        outline: none;
        transition: border-color 0.2s, background-color 0.2s;
    }

    .slack-search-input:focus {
        border-color: #0F62FE;
        background: #ffffff;
    }

    .slack-search-input::placeholder {
        color: #94a3b8;
    }

    .slack-chat-list {
        flex: 1;
        overflow-y: auto;
        padding: 6px;
    }

    .slack-chat-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 12px 14px;
        cursor: pointer;
        border-radius: 12px;
        transition: background 0.2s;
        text-decoration: none;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .slack-chat-item:hover {
        background: #D3D3D3;
    }

    .slack-chat-item.active {
        background: #0F62FE;
        color: #ffffff;
    }

    .slack-chat-name-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        font-size: 13.5px;
        color: #1e293b;
    }

    .slack-chat-item.active .slack-chat-name-row {
        color: #ffffff;
    }

    .review-stars {
        color: #fbbf24;
        font-size: 11px;
    }

    .slack-chat-message {
        font-size: 12px;
        color: #64748b;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.4;
        margin-top: 3px;
    }

    .slack-chat-item.active .slack-chat-message {
        color: rgba(255, 255, 255, 0.8);
    }

    .product-context {
        font-size: 11px;
        color: #0F62FE;
        font-weight: 600;
    }

    .slack-chat-item.active .product-context {
        color: #93c5fd;
    }

    /* Middle Chat Area */
    .slack-chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #D3D3D3;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }

    .slack-chat-header {
        height: 60px;
        border-bottom: 1px solid #e2e8f0;
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #D3D3D3;
        color: #1e293b;
        flex-shrink: 0;
    }

    .slack-chat-header-title {
        font-weight: 700;
        font-size: 15px;
        color: #1e293b;
    }

    .slack-chat-header-status {
        font-size: 11px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 2px;
        font-weight: 500;
    }

    .slack-messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        background: #D3D3D3;
    }

    /* Chat bubble styling */
    .slack-msg-row {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .slack-msg-row.customer {
        background: #ffffff;
        border: 1px solid #cce0ff;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 10px;
    }

    .slack-msg-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #D3D3D3;
        color: #475569;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 13px;
        flex-shrink: 0;
    }

    .slack-msg-row.customer .slack-msg-avatar {
        background: #ffd8a8;
        color: #e8590c;
    }

    .slack-msg-row.admin .slack-msg-avatar {
        background: #cc5de8;
        color: #862e9c;
    }

    .slack-msg-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }

    .slack-msg-row.admin .slack-msg-body {
        align-items: flex-end;
    }

    .slack-msg-name-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 4px;
    }

    .slack-msg-username {
        font-weight: 700;
        font-size: 13.5px;
        color: #1e293b;
    }

    .slack-msg-role-tag {
        font-size: 9px;
        background: #e2e8f0;
        color: #475569;
        padding: 1px 6px;
        border-radius: 4px;
        font-weight: 600;
        text-transform: uppercase;
    }

    .slack-msg-bubble {
        padding: 12px 16px;
        border-radius: 14px;
        border-top-left-radius: 2px;
        font-size: 14px;
        line-height: 1.5;
        word-break: break-word;
        color: #ffffff;
        background: #0F62FE;
    }

    .slack-msg-row.admin .slack-msg-bubble {
        border-top-left-radius: 14px;
        border-top-right-radius: 2px;
    }

    .slack-msg-row.customer .slack-msg-bubble {
        background: transparent;
        color: #1e293b;
        padding: 0;
        border-radius: 0;
    }

    .slack-msg-time {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 4px;
        padding: 0 4px;
    }

    .review-photo-preview {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .review-photo-preview:hover {
        transform: scale(1.05);
    }

    /* Input Bar */
    .slack-input-container {
        padding: 16px 24px;
        background: #D3D3D3;
        border-top: 1px solid #e2e8f0;
        flex-shrink: 0;
    }

    .slack-input-pill {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 6px 16px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .slack-input-field {
        flex: 1;
        background: transparent;
        border: none;
        outline: none;
        color: #1e293b;
        font-size: 14px;
        padding: 6px 0;
        resize: none;
        height: 24px;
    }

    .slack-input-field::placeholder {
        color: #94a3b8;
    }

    .slack-send-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #0F62FE;
        color: #ffffff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s, background-color 0.2s;
    }

    .slack-send-btn:hover {
        transform: scale(1.05);
        background: #0b56db;
    }

    /* Right Details Pane */
    .slack-details-pane {
        width: 300px;
        background: #D3D3D3;
        border-radius: 16px;
        display: flex;
        flex-direction: column;
        padding: 24px;
        overflow-y: auto;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .slack-details-section {
        margin-bottom: 24px;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 20px;
    }

    .slack-details-section:last-child {
        border-bottom: none;
    }

    .slack-details-title {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 12px;
        letter-spacing: 0.5px;
    }

    /* ── Dark Mode Adaptations ── */
    [data-theme="dark"] .slack-chat-workspace {
        background: #1A1D21;
        color: #f1f5f9;
    }

    [data-theme="dark"] .slack-sidebar {
        background: #2D3139;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    [data-theme="dark"] .slack-sidebar-header {
        background: #2D3139;
        color: #f1f5f9;
        border-bottom: 1px solid #3d4350;
    }

    [data-theme="dark"] .slack-search-bar {
        border-bottom: 1px solid #3d4350;
    }

    [data-theme="dark"] .slack-search-input {
        background: #3d4350;
        border: 1px solid #4d5566;
        color: #f1f5f9;
    }

    [data-theme="dark"] .slack-search-input:focus {
        border-color: #0F62FE;
        background: #2D3139;
    }

    [data-theme="dark"] .slack-chat-item {
        color: #cbd5e1;
    }

    [data-theme="dark"] .slack-chat-item:hover {
        background: #3d4350;
    }

    [data-theme="dark"] .slack-chat-item.active {
        background: #0F62FE;
        color: #ffffff;
    }

    [data-theme="dark"] .slack-chat-name-row {
        color: #f1f5f9;
    }

    [data-theme="dark"] .slack-chat-item.active .slack-chat-name-row {
        color: #ffffff;
    }

    [data-theme="dark"] .slack-chat-message {
        color: #94a3b8;
    }

    [data-theme="dark"] .slack-chat-item.active .slack-chat-message {
        color: rgba(255, 255, 255, 0.8);
    }

    [data-theme="dark"] .slack-chat-area {
        background: #2D3139;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    [data-theme="dark"] .slack-chat-header {
        border-bottom: 1px solid #3d4350;
        background: #2D3139;
        color: #f1f5f9;
    }

    [data-theme="dark"] .slack-chat-header-title {
        color: #f1f5f9;
    }

    [data-theme="dark"] .slack-messages-container {
        background: #2D3139;
    }

    [data-theme="dark"] .slack-msg-row.customer {
        background: #3d4350;
        border-color: #4d5566;
    }

    [data-theme="dark"] .slack-msg-avatar {
        background: #3d4350;
        color: #cbd5e1;
    }

    [data-theme="dark"] .slack-msg-username {
        color: #f1f5f9;
    }

    [data-theme="dark"] .slack-msg-role-tag {
        background: #4d5566;
        color: #cbd5e1;
    }

    [data-theme="dark"] .slack-msg-row.customer .slack-msg-bubble {
        background: transparent;
        color: #f1f5f9;
    }

    [data-theme="dark"] .slack-msg-row.admin .slack-msg-bubble {
        background: #0F62FE;
        color: #ffffff;
    }

    [data-theme="dark"] .slack-input-container {
        background: #2D3139;
        border-top: 1px solid #3d4350;
    }

    [data-theme="dark"] .slack-input-pill {
        background: #3d4350;
        border: 1px solid #4d5566;
    }

    [data-theme="dark"] .slack-input-field {
        color: #f1f5f9;
    }

    [data-theme="dark"] .slack-details-pane {
        background: #2D3139;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
    }

    [data-theme="dark"] .slack-details-section {
        border-bottom: 1px solid #3d4350;
    }

    [data-theme="dark"] .slack-details-title {
        color: #94a3b8;
    }

    [data-theme="dark"] .slack-details-section img {
        border-color: #4d5566 !important;
    }

    [data-theme="dark"] .slack-details-section div,
    [data-theme="dark"] .slack-details-section span,
    [data-theme="dark"] .slack-details-section button {
        color: inherit;
    }

    [data-theme="dark"] .slack-details-section div[style*="color:#1e293b"],
    [data-theme="dark"] .slack-details-section div[style*="color:#fff"] {
        color: #f1f5f9 !important;
    }

    [data-theme="dark"] .slack-details-section span[style*="color:#94a3b8"] {
        color: #94a3b8 !important;
    }

    [data-theme="dark"] .slack-details-section div[style*="background:#e2e8f0"] {
        background: #3d4350 !important;
        color: #cbd5e1 !important;
    }

    [data-theme="dark"] #threeDotsDropdown {
        background: #1A1D21 !important;
        border-color: #3d4350 !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5) !important;
    }

    [data-theme="dark"] #threeDotsDropdown a {
        color: #ffffff !important;
    }

    [data-theme="dark"] #threeDotsDropdown a:hover {
        background: #3d4350 !important;
    }
</style>
@endpush

@section('content')
<div class="slack-chat-workspace">
    <!-- Left Sidebar: Review List -->
    <div class="slack-sidebar">
        <div class="slack-sidebar-header">
            <span>💬 Chat Ulasan</span>
        </div>
        <div class="slack-search-bar">
            <form method="GET" action="{{ route('admin.review-chats.index') }}" style="display: flex; gap: 6px;">
                <input type="text" name="search" class="slack-search-input"
                    placeholder="🔍 Cari nama, email, ulasan..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm" style="background-color: #3C3565; color: #ffffff; border: none; padding: 0 12px; border-radius: 8px; font-weight: 600; flex-shrink: 0; display: flex; align-items: center; justify-content: center; gap: 4px;" title="Cari Ulasan">
                    <iconify-icon icon="lucide:search" style="font-size: 14px;"></iconify-icon>
                    Cari
                </button>
                @if(request('search'))
                <a href="{{ route('admin.review-chats.index') }}" class="btn btn-sm" style="background: var(--bg-card, #ffffff); color: var(--text-primary, #1e293b); border: 1px solid var(--border, #cbd5e1); border-radius: 8px; padding: 0 10px; display: flex; align-items: center; justify-content: center; font-weight: 600;" title="Reset Search">
                    <iconify-icon icon="lucide:x" style="font-size: 15px; color: var(--text-primary, #1e293b);"></iconify-icon>
                </a>
                @endif
            </form>
        </div>
        <div class="slack-chat-list">
            @forelse($reviews as $r)
            @php
            $isActive = isset($review) && $r->id === $review->id;
            @endphp
            <a href="{{ route('admin.review-chats.show', $r) }}" class="slack-chat-item {{ $isActive ? 'active' : '' }}">
                <div class="slack-chat-name-row">
                    <span class="text-truncate">{{ $r->user?->name ?? 'User' }}</span>
                    <span class="review-stars">
                        @for($i=1; $i<=5; $i++)
                            @if($i <=$r->rating)
                            ★
                            @else
                            ☆
                            @endif
                            @endfor
                    </span>
                </div>
                <div class="product-context text-truncate">
                    📦 {{ $r->product?->name }}
                </div>
                <div class="slack-chat-message">
                    {{ $r->comment ?? '(Tidak ada komentar)' }}
                </div>
            </a>
            @empty
            <div style="text-align:center;color:#94a3b8;padding:40px 10px;font-size:13px;">
                Belum ada ulasan dengan komentar.
            </div>
            @endforelse
        </div>
    </div>

    <!-- Middle: Chat Conversation Area -->
    <div class="slack-chat-area">
        @if(isset($review))
        <div class="slack-chat-header">
            <div>
                <div class="slack-chat-header-title">Ulasan: {{ $review->user?->name }}</div>
                <div class="slack-chat-header-status">
                    <span class="review-stars">
                        @for($i=1; $i<=5; $i++)
                            @if($i <=$review->rating)
                            ★
                            @else
                            ☆
                            @endif
                            @endfor
                    </span>
                    <span>• Rating {{ $review->rating }}/5</span>
                </div>
            </div>
            <div style="position:relative; display:inline-block;">
                <i class="bi bi-three-dots-vertical" id="threeDotsBtn" style="color: #64748b; font-size: 18px; cursor: pointer; padding: 4px;"></i>

                {{-- Dropdown Menu --}}
                <div id="threeDotsDropdown" style="display:none; position:absolute; right:0; top:30px; background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; width:160px; box-shadow:0 8px 20px rgba(0,0,0,0.08); z-index:1050; padding:4px 0;">
                    <a href="javascript:void(0);" id="toggleDetailsBtn" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#1e293b; font-size:13px; text-decoration:none; transition:background 0.2s;">
                        <i class="bi bi-info-circle"></i> Toggle Info
                    </a>
                </div>
            </div>
        </div>

        {{-- Bubbles Scrollable Area --}}
        <div class="slack-messages-container" id="chatContainer">
            <!-- Customer Original Review -->
            <div class="slack-msg-row customer">
                <div class="slack-msg-avatar">
                    {{ strtoupper(substr($review->user?->name ?? 'C', 0, 1)) }}
                </div>
                <div class="slack-msg-body">
                    <div class="slack-msg-name-row">
                        <span class="slack-msg-username">{{ $review->user?->name }}</span>
                        <span class="review-stars">
                            @for($i=1; $i<=5; $i++)
                                @if($i <=$review->rating)
                                ★
                                @else
                                ☆
                                @endif
                                @endfor
                        </span>
                    </div>
                    <div class="slack-msg-bubble">
                        <p style="margin: 0; font-size: 14px;">{{ $review->comment ?? '(Tidak ada komentar)' }}</p>

                        @if(!empty($review->photos))
                        <div style="display: flex; gap: 8px; margin-top: 10px;">
                            @foreach($review->photos as $photo)
                            @php
                            $photoUrl = str_starts_with($photo, 'http') ? $photo : asset('storage/' . $photo);
                            @endphp
                            <img src="{{ $photoUrl }}" class="review-photo-preview" onclick="window.open('{{ $photoUrl }}', '_blank')" />
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="slack-msg-time">
                        Ulasan dikirim pada {{ $review->created_at->format('d M Y, H:i') }}
                    </div>
                </div>
            </div>

            <!-- Admin replies thread -->
            @forelse($review->replies as $r)
            <div class="slack-msg-row admin">
                <div class="slack-msg-avatar">
                    A
                </div>
                <div class="slack-msg-body">
                    <div class="slack-msg-name-row">
                        <span class="slack-msg-username">{{ $r->user?->name ?? 'Admin' }}</span>
                        <span class="slack-msg-role-tag">Staff</span>
                    </div>
                    <div class="slack-msg-bubble">
                        {{ $r->reply }}
                    </div>
                    <div class="slack-msg-time">
                        {{ $r->created_at->format('H:i') }}
                    </div>
                </div>
            </div>
            @empty
            <div style="text-align:center;color:#94a3b8;padding:20px 0;font-size:13px;" id="noRepliesPlaceholder">
                Belum ada respon untuk ulasan ini. Ketik pesan di bawah untuk membalas.
            </div>
            @endforelse
        </div>

        {{-- Reply Input Bar --}}
        <div class="slack-input-container">
            <form method="POST" action="{{ route('admin.review-chats.reply', $review) }}" id="replyForm">
                @csrf
                <div class="slack-input-pill">
                    <input type="text" name="message" id="messageInput" class="slack-input-field"
                        placeholder="Tulis balasan ulasan..." required autocomplete="off" />
                    <button type="submit" class="slack-send-btn" id="sendBtn">
                        <i class="bi bi-send-fill" style="font-size: 14px;"></i>
                    </button>
                </div>
            </form>
        </div>
        @else
        <div class="slack-chat-header">
            <div class="slack-chat-header-title">Detail Obrolan</div>
        </div>
        <div class="slack-messages-container" style="justify-content:center; align-items:center; text-align:center; min-height:300px;">
            <div style="font-size:48px; margin-bottom:16px;">💬</div>
            <h3 style="color:#64748b; font-weight:700; margin-bottom:8px;">Selamat Datang di Chat Ulasan</h3>
            <p style="color:#64748b; font-size:13px; max-width:320px; margin:0 auto; line-height:1.6;">
                Pilih salah satu ulasan customer di panel kiri untuk mulai membalas ulasan secara real-time.
            </p>
        </div>
        @endif
    </div>

    <!-- Right Pane: Context & Customer Details -->
    <div class="slack-details-pane">
        @if(isset($review))
        <!-- Section: Product queried -->
        <div class="slack-details-section">
            <div class="slack-details-title">Produk Yang Diulas</div>
            @if($review->product)
            @if($review->product->main_photo)
            @php
            $photoUrl = str_starts_with($review->product->main_photo, 'http') ? $review->product->main_photo : asset('storage/' . $review->product->main_photo);
            @endphp
            <img src="{{ $photoUrl }}" style="width:100%; border-radius:10px; margin-bottom:12px; border:1px solid #e2e8f0; background:#fff; display:block;" />
            @endif
            <div style="font-weight:700; font-size:14.5px; color:#1e293b; line-height:1.4;">{{ $review->product->name }}</div>
            <div style="color:#0F62FE; font-weight:700; font-size:15px; margin-top:6px;">
                Rp. {{ number_format($review->product->price, 0, ',', '.') }}
            </div>
            @else
            <div style="color:#94a3b8; font-size:13px; text-align:center; padding:16px 0;">
                Detail produk tidak tersedia.
            </div>
            @endif
        </div>

        <!-- Section: Customer Info -->
        <div class="slack-details-section">
            <div class="slack-details-title">Info Pembeli</div>
            <div style="display:flex; flex-direction:column; gap:8px; font-size:13px;">
                <div>
                    <span style="color:#94a3b8;">Nama:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px;">{{ $review->user?->name ?? '-' }}</div>
                </div>
                <div>
                    <span style="color:#94a3b8;">Email:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px; word-break:break-all;">{{ $review->user?->email ?? '-' }}</div>
                </div>
                <div>
                    <span style="color:#94a3b8;">Daftar Akun:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px;">{{ $review->user?->created_at ? $review->user->created_at->format('d M Y') : '-' }}</div>
                </div>
            </div>
        </div>
        @else
        <div class="slack-details-section">
            <div class="slack-details-title">Detail Informasi</div>
            <div style="text-align:center; color:#94a3b8; font-size:13px; padding-top:40px;">
                Tidak ada ulasan aktif.
            </div>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Auto-scroll ke bawah saat halaman load
    const chatContainer = document.getElementById('chatContainer');
    if (chatContainer) chatContainer.scrollTop = chatContainer.scrollHeight;

    // Submit form dengan Enter (Shift+Enter = new line)
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.focus();
        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                document.getElementById('replyForm')?.requestSubmit();
            }
        });
    }

    // Intercept form submission and use AJAX to reply
    const replyForm = document.getElementById('replyForm');
    if (replyForm) {
        replyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const msg = messageInput.value.trim();
            if (!msg) return;

            fetch(this.action, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        message: msg
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        messageInput.value = '';
                        // Append message element
                        const placeholder = document.getElementById('noRepliesPlaceholder');
                        if (placeholder) placeholder.remove();

                        const msgRow = document.createElement('div');
                        msgRow.className = 'slack-msg-row admin';
                        msgRow.innerHTML = `
                    <div class="slack-msg-avatar">A</div>
                    <div class="slack-msg-body">
                        <div class="slack-msg-name-row">
                            <span class="slack-msg-username">${data.reply.admin_name}</span>
                            <span class="slack-msg-role-tag">Staff</span>
                        </div>
                        <div class="slack-msg-bubble">
                            ${data.reply.reply}
                        </div>
                        <div class="slack-msg-time">
                            Baru saja
                        </div>
                    </div>
                `;
                        chatContainer.appendChild(msgRow);
                        chatContainer.scrollTop = chatContainer.scrollHeight;
                    }
                })
                .catch(err => console.error(err));
        });
    }

    // Handling Three Dots Dropdown Toggle
    const threeDotsBtn = document.getElementById('threeDotsBtn');
    const threeDotsDropdown = document.getElementById('threeDotsDropdown');
    const toggleDetailsBtn = document.getElementById('toggleDetailsBtn');
    const detailsPane = document.querySelector('.slack-details-pane');

    if (threeDotsBtn && threeDotsDropdown) {
        threeDotsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            threeDotsDropdown.style.display = threeDotsDropdown.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', (e) => {
            if (threeDotsDropdown && !threeDotsDropdown.contains(e.target) && e.target !== threeDotsBtn) {
                threeDotsDropdown.style.display = 'none';
            }
        });
    }

    if (toggleDetailsBtn && detailsPane) {
        toggleDetailsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            if (detailsPane.style.display === 'none') {
                detailsPane.style.display = 'flex';
                localStorage.setItem('hideReviewDetailsPane', 'false');
            } else {
                detailsPane.style.display = 'none';
                localStorage.setItem('hideReviewDetailsPane', 'true');
            }
            if (threeDotsDropdown) threeDotsDropdown.style.display = 'none';
        });

        // Restore state from localStorage
        const shouldHide = localStorage.getItem('hideReviewDetailsPane') === 'true';
        if (shouldHide) {
            detailsPane.style.display = 'none';
        }
    }

    // Auto-refresh bubble setiap 5 detik (polling)
    const isReviewActive = @json(isset($review));
    if (isReviewActive) {
        setInterval(function() {
            fetch(window.location.href, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.text())
                .then(html => {
                    const parser = new window.DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newChat = doc.getElementById('chatContainer');
                    const curChat = document.getElementById('chatContainer');
                    if (newChat && curChat) {
                        const wasAtBottom = curChat.scrollTop + curChat.clientHeight >= curChat.scrollHeight - 20;
                        curChat.innerHTML = newChat.innerHTML;
                        if (wasAtBottom) curChat.scrollTop = curChat.scrollHeight;
                    }
                }).catch(() => {});
        }, 5000);
    }
</script>
@endpush
@endsection