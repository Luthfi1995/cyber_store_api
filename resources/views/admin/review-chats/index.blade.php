@extends('admin.layouts.app')
@section('title', isset($review) ? 'Chat Ulasan - ' . $review->user?->name : 'Chat Ulasan')
@section('page-title', 'Chat Ulasan')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <span>Chat Ulasan</span>
@endsection

@push('styles')
<style>
    /* ── Slack-like Review Workspace ── */
    .slack-chat-workspace {
        display: flex;
        height: calc(100vh - 120px);
        background: #121316;
        border-radius: 12px;
        overflow: hidden;
        color: #e2e8f0;
        font-family: 'Inter', sans-serif;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    /* Left Sidebar */
    .slack-sidebar {
        width: 300px;
        background: #1A1D21;
        border-right: 1px solid #2B2E33;
        display: flex;
        flex-direction: column;
        flex-shrink: 0;
    }
    .slack-sidebar-header {
        padding: 16px 20px;
        font-size: 16px;
        font-weight: 700;
        color: #ffffff;
        border-bottom: 1px solid #2B2E33;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .slack-search-bar {
        padding: 12px;
        border-bottom: 1px solid #2B2E33;
    }
    .slack-search-input {
        width: 100%;
        background: #2D3136;
        border: none;
        border-radius: 6px;
        padding: 8px 12px;
        color: #ffffff;
        font-size: 13px;
        outline: none;
    }
    .slack-search-input::placeholder {
        color: #94a3b8;
    }
    .slack-chat-list {
        flex: 1;
        overflow-y: auto;
        padding: 10px 0;
    }
    .slack-chat-item {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 14px 20px;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid #22252a;
    }
    .slack-chat-item:hover {
        background: #22252A;
    }
    .slack-chat-item.active {
        background: #2B2E33;
        border-left: 3px solid #2563EB;
    }
    .slack-chat-name-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        font-size: 13.5px;
        color: #ffffff;
    }
    .review-stars {
        color: #fbbf24;
        font-size: 11px;
    }
    .slack-chat-message {
        font-size: 12px;
        color: #94a3b8;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        line-height: 1.4;
    }
    .product-context {
        font-size: 11px;
        color: #3b82f6;
        font-weight: 500;
    }

    /* Middle Chat Area */
    .slack-chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #121316;
    }
    .slack-chat-header {
        height: 60px;
        border-bottom: 1px solid #2B2E33;
        padding: 0 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #1A1D21;
        flex-shrink: 0;
    }
    .slack-chat-header-title {
        font-weight: 700;
        font-size: 15px;
        color: #ffffff;
    }
    .slack-chat-header-status {
        font-size: 11px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 2px;
    }
    .slack-messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Message Row & Bubbles */
    .slack-msg-row {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    .slack-msg-row.customer {
        /* User's original review at the top */
        background: rgba(37, 99, 235, 0.05);
        border: 1px solid rgba(37, 99, 235, 0.15);
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 10px;
    }
    .slack-msg-avatar {
        width: 36px; height: 36px;
        border-radius: 50%;
        background: #64748b;
        color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 13px;
        flex-shrink: 0;
    }
    .slack-msg-row.customer .slack-msg-avatar {
        background: #2563EB;
    }
    .slack-msg-row.admin .slack-msg-avatar {
        background: #0d47a1;
    }
    .slack-msg-body {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
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
        color: #ffffff;
    }
    .slack-msg-role-tag {
        font-size: 9px;
        background: #2B2E33;
        color: #94a3b8;
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
        background: #2B2E33;
    }
    .slack-msg-row.customer .slack-msg-bubble {
        background: transparent;
        padding: 0;
        border-radius: 0;
    }
    .slack-msg-time {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 4px;
    }
    .review-photo-preview {
        width: 80px; height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid #2B2E33;
        cursor: pointer;
        transition: transform 0.2s;
    }
    .review-photo-preview:hover {
        transform: scale(1.05);
    }

    /* Input Bar */
    .slack-input-container {
        padding: 16px 24px;
        background: #121316;
        flex-shrink: 0;
    }
    .slack-input-pill {
        background: #2D3136;
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
        color: #ffffff;
        font-size: 14px;
        padding: 6px 0;
        resize: none;
        height: 24px;
    }
    .slack-input-field::placeholder {
        color: #94a3b8;
    }
    .slack-send-btn {
        width: 32px; height: 32px;
        border-radius: 50%;
        background: #2563EB;
        color: #ffffff;
        border: none;
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: transform 0.2s, background-color 0.2s;
    }
    .slack-send-btn:hover {
        transform: scale(1.05);
        background: #3b82f6;
    }

    /* Right Details Pane */
    .slack-details-pane {
        width: 300px;
        background: #1A1D21;
        border-left: 1px solid #2B2E33;
        display: flex;
        flex-direction: column;
        padding: 24px;
        overflow-y: auto;
        flex-shrink: 0;
    }
    .slack-details-section {
        margin-bottom: 24px;
        border-bottom: 1px solid #2B2E33;
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
            <form method="GET" action="{{ route('admin.review-chats.index') }}">
                <input type="text" name="search" class="slack-search-input"
                    placeholder="🔍 Cari ulasan..." value="{{ request('search') }}">
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
                                @if($i <= $r->rating)
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
                                @if($i <= $review->rating)
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
                    <i class="bi bi-three-dots-vertical" id="threeDotsBtn" style="color: #94a3b8; font-size: 18px; cursor: pointer; padding: 4px;"></i>
                    
                    {{-- Dropdown Menu --}}
                    <div id="threeDotsDropdown" style="display:none; position:absolute; right:0; top:30px; background:#1A1D21; border:1px solid #2B2E33; border-radius:8px; width:160px; box-shadow:0 8px 20px rgba(0,0,0,0.5); z-index:1050; padding:4px 0;">
                        <a href="javascript:void(0);" id="toggleDetailsBtn" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#ffffff; font-size:13px; text-decoration:none; transition:background 0.2s;">
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
                                    @if($i <= $review->rating)
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
                <h3 style="color:#ffffff; font-weight:700; margin-bottom:8px;">Selamat Datang di Chat Ulasan</h3>
                <p style="color:#94a3b8; font-size:13px; max-width:320px; margin:0 auto; line-height:1.6;">
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
                        <img src="{{ $photoUrl }}" style="width:100%; border-radius:10px; margin-bottom:12px; border:1px solid #2B2E33; background:#fff; display:block;" />
                    @endif
                    <div style="font-weight:700; font-size:14.5px; color:#ffffff; line-height:1.4;">{{ $review->product->name }}</div>
                    <div style="color:#3b82f6; font-weight:700; font-size:15px; margin-top:6px;">
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
                        <div style="font-weight:600; color:#fff; margin-top:2px;">{{ $review->user?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <span style="color:#94a3b8;">Email:</span>
                        <div style="font-weight:600; color:#fff; margin-top:2px; word-break:break-all;">{{ $review->user?->email ?? '-' }}</div>
                    </div>
                    <div>
                        <span style="color:#94a3b8;">Daftar Akun:</span>
                        <div style="font-weight:600; color:#fff; margin-top:2px;">{{ $review->user?->created_at ? $review->user->created_at->format('d M Y') : '-' }}</div>
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
            body: JSON.stringify({ message: msg })
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
@if(isset($review))
setInterval(function () {
    fetch(window.location.href, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(res => res.text())
        .then(html => {
            const parser  = new DOMParser();
            const doc     = parser.parseFromString(html, 'text/html');
            const newChat = doc.getElementById('chatContainer');
            const curChat = document.getElementById('chatContainer');
            if (newChat && curChat) {
                const wasAtBottom = curChat.scrollTop + curChat.clientHeight >= curChat.scrollHeight - 20;
                curChat.innerHTML = newChat.innerHTML;
                if (wasAtBottom) curChat.scrollTop = curChat.scrollHeight;
            }
        }).catch(() => {});
}, 5000);
@endif
</script>
@endpush
@endsection
