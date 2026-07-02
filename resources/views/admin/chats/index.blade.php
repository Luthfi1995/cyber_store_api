@extends('admin.layouts.app')
@section('title', isset($chat) ? 'Chat dengan ' . $chat->customer?->name : 'Chat Customer')
@section('page-title', 'Support Chat')
@section('breadcrumb')
    <span class="breadcrumb-sep">›</span>
    <span>Support Chat</span>
@endsection

@push('styles')
<style>
    /* ── Slack Workspace Style ── */
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
        width: 280px;
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
        align-items: center;
        gap: 12px;
        padding: 12px 20px;
        cursor: pointer;
        transition: background 0.2s;
        text-decoration: none;
        color: inherit;
    }
    .slack-chat-item:hover {
        background: #22252A;
    }
    .slack-chat-item.active {
        background: #2B2E33;
        border-left: 3px solid #2563EB;
    }
    .slack-chat-avatar {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: #4f6ef7;
        color: #ffffff;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700; font-size: 14px;
        flex-shrink: 0;
    }
    .slack-chat-details {
        flex: 1;
        min-width: 0;
    }
    .slack-chat-name-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 600;
        font-size: 13.5px;
        color: #ffffff;
    }
    .slack-chat-message {
        font-size: 12px;
        color: #94a3b8;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-top: 3px;
    }
    .slack-chat-badge {
        background: #ef4444;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 999px;
        margin-left: 6px;
        flex-shrink: 0;
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
        color: #10B981;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 2px;
        font-weight: 500;
    }
    .slack-chat-header-status.closed {
        color: #ef4444;
    }
    .slack-messages-container {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
    }

    /* Chat bubble styling matching Slack screenshot */
    .slack-msg-row {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }
    .slack-msg-row.customer {
        flex-direction: row-reverse;
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
    .slack-msg-body {
        max-width: 70%;
        display: flex;
        flex-direction: column;
    }
    .slack-msg-row.customer .slack-msg-body {
        align-items: flex-end;
    }
    .slack-msg-row.admin .slack-msg-body {
        align-items: flex-start;
    }
    .slack-msg-bubble {
        padding: 12px 16px;
        border-radius: 14px;
        font-size: 14px;
        line-height: 1.5;
        word-break: break-word;
        color: #ffffff;
    }
    .slack-msg-row.customer .slack-msg-bubble {
        background: #2563EB;
        border-top-right-radius: 2px;
    }
    .slack-msg-row.admin .slack-msg-bubble {
        background: #2B2E33;
        border-top-left-radius: 2px;
    }
    .slack-msg-time {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 4px;
        padding: 0 4px;
    }

    /* Input Bar matching Slack screenshot */
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
    .slack-input-icon {
        color: #94a3b8;
        font-size: 18px;
        cursor: pointer;
        transition: color 0.2s;
        display: flex;
        align-items: center;
    }
    .emoji-item:hover {
        background: #2B2E33;
    }
    #threeDotsDropdown a:hover {
        background: #2B2E33 !important;
    }
    .slack-input-icon:hover {
        color: #ffffff;
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
    <!-- Left Sidebar: Chat List -->
    <div class="slack-sidebar">
        <div class="slack-sidebar-header">
            <span>💬 Customer Support</span>
        </div>
        <div class="slack-search-bar">
            <form method="GET" action="{{ route('admin.chats.index') }}">
                <input type="text" name="search" class="slack-search-input"
                    placeholder="🔍 Cari customer..." value="{{ request('search') }}">
            </form>
        </div>
        <div class="slack-chat-list">
            @forelse($chats as $c)
                @php
                    $initials = strtoupper(substr($c->customer?->name ?? 'C', 0, 1));
                    $unread = $c->unread_count;
                    $isActive = isset($chat) && $c->id === $chat->id;
                @endphp
                <a href="{{ route('admin.chats.show', $c) }}" class="slack-chat-item {{ $isActive ? 'active' : '' }}">
                    <div class="slack-chat-avatar">{{ $initials }}</div>
                    <div class="slack-chat-details">
                        <div class="slack-chat-name-row">
                            <span class="text-truncate">{{ $c->customer?->name ?? 'Customer' }}</span>
                            @if($unread > 0)
                                <span class="slack-chat-badge">{{ $unread }}</span>
                            @endif
                        </div>
                        <div class="slack-chat-message">
                            @if($c->product_name)
                                <span style="color:#4f6ef7;">[📦 {{ $c->product_name }}]</span>
                            @endif
                            {{ $c->lastMessage?->message ?? 'Memulai percakapan...' }}
                        </div>
                    </div>
                </a>
            @empty
                <div style="text-align:center;color:#94a3b8;padding:40px 10px;font-size:13px;">
                    Belum ada chat masuk.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Middle: Chat Conversation Area -->
    <div class="slack-chat-area">
        @if(isset($chat))
            <div class="slack-chat-header">
                <div>
                    <div class="slack-chat-header-title">{{ $chat->customer?->name ?? 'Customer' }}</div>
                    @if($chat->status === 'open')
                        <div class="slack-chat-header-status">
                            <span style="font-size: 8px;">●</span> Online
                        </div>
                    @else
                        <div class="slack-chat-header-status closed">
                            <span style="font-size: 8px;">●</span> Closed
                        </div>
                    @endif
                </div>
                <div style="position:relative; display:inline-block;">
                    <i class="bi bi-three-dots-vertical" id="threeDotsBtn" style="color: #94a3b8; font-size: 18px; cursor: pointer; padding: 4px;"></i>
                    
                    {{-- Dropdown Menu --}}
                    <div id="threeDotsDropdown" style="display:none; position:absolute; right:0; top:30px; background:#1A1D21; border:1px solid #2B2E33; border-radius:8px; width:160px; box-shadow:0 8px 20px rgba(0,0,0,0.5); z-index:1050; padding:4px 0;">
                        <a href="javascript:void(0);" id="toggleDetailsBtn" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#ffffff; font-size:13px; text-decoration:none; transition:background 0.2s;">
                            <i class="bi bi-info-circle"></i> Toggle Info
                        </a>
                        @if($chat->status === 'open')
                            <a href="javascript:void(0);" onclick="if(confirm('Tutup percakapan ini?')) document.getElementById('dropdownCloseForm').submit();" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#ef4444; font-size:13px; text-decoration:none; transition:background 0.2s;">
                                <i class="bi bi-x-circle"></i> Tutup Chat
                            </a>
                            <form id="dropdownCloseForm" action="{{ route('admin.chats.close', $chat) }}" method="POST" style="display:none;">
                                @csrf
                            </form>
                        @else
                            <a href="javascript:void(0);" onclick="document.getElementById('dropdownReopenForm').submit();" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#3b82f6; font-size:13px; text-decoration:none; transition:background 0.2s;">
                                <i class="bi bi-arrow-counterclockwise"></i> Buka Kembali
                            </a>
                            <form id="dropdownReopenForm" action="{{ route('admin.chats.reopen', $chat) }}" method="POST" style="display:none;">
                                @csrf
                            </form>
                        @endif
                    </div>
                </div>
            </div>
            
            {{-- Bubbles Scrollable Area --}}
            <div class="slack-messages-container" id="chatContainer">
                @forelse($chat->messages as $msg)
                    @php
                        $isSelf = $msg->sender_type === 'admin';
                        $avatarText = $isSelf ? 'A' : strtoupper(substr($chat->customer?->name ?? 'C', 0, 1));
                        
                        $msgText = $msg->message;
                        $bubbleStyle = '';
                        if (str_starts_with($msgText, '[IMAGE]:')) {
                            $base64 = substr($msgText, 8);
                            $msgContent = '<img src="' . $base64 . '" style="max-width:260px; border-radius:10px; display:block;" />';
                        } elseif (str_starts_with($msgText, '[STICKER]:')) {
                            $stickerUrl = substr($msgText, 10);
                            $msgContent = '<img src="' . e($stickerUrl) . '" style="width:100px; height:100px; display:block;" />';
                            $bubbleStyle = 'background:transparent; box-shadow:none; padding:0;';
                        } else {
                            $msgContent = e($msgText);
                        }
                    @endphp
                    
                    <div class="slack-msg-row {{ $msg->sender_type }}">
                        <div class="slack-msg-avatar">{{ $avatarText }}</div>
                        <div class="slack-msg-body">
                            <div class="slack-msg-bubble" style="{{ $bubbleStyle }}">
                                {!! $msgContent !!}
                            </div>
                            <div class="slack-msg-time">{{ $msg->created_at->format('H:i') }}</div>
                        </div>
                    </div>
                @empty
                    <div style="text-align:center;color:#94a3b8;padding:40px 0;font-size:14px;">
                        Belum ada pesan.
                    </div>
                @endforelse
            </div>

            {{-- Reply Input Bar --}}
            <div class="slack-input-container">
                @if($chat->status === 'open')
                    <form method="POST" action="{{ route('admin.chats.reply', $chat) }}" id="replyForm">
                        @csrf
                        <div class="slack-input-pill">
                            <!-- Input File Tersembunyi untuk Gambar -->
                            <input type="file" id="imageFileInput" accept="image/*" style="display:none;" />
                            
                            <div class="slack-input-icon" id="paperclipBtn" title="Kirim Gambar">
                                <i class="bi bi-paperclip"></i>
                            </div>
                            <div class="slack-input-icon">
                                <i class="bi bi-mic"></i>
                            </div>
                            <input type="text" name="message" id="messageInput" class="slack-input-field" 
                                   placeholder="Write a message..." required autocomplete="off" />
                            
                            <!-- Emoji Popover Container -->
                            <div style="position:relative; display:flex; align-items:center;">
                                <div class="slack-input-icon" id="stickerBtn" title="Pilih Emoji">
                                    <i class="bi bi-emoji-smile"></i>
                                </div>
                                
                                {{-- Emoji Picker Popover --}}
                                <div id="stickerPopover" style="display:none; position:absolute; bottom:40px; right:0; background:#1A1D21; border:1px solid #2B2E33; border-radius:12px; padding:12px; width:280px; box-shadow:0 10px 25px rgba(0,0,0,0.5); z-index:1000;">
                                    <div style="font-size:11px; font-weight:700; color:#94a3b8; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;">Pilih Emoji</div>
                                    <div style="display:grid; grid-template-columns: repeat(7, 1fr); gap:6px; max-height:160px; overflow-y:auto; font-size:20px; text-align:center;">
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😀</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😂</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😊</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😍</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🤣</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🥰</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😘</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😎</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😭</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🥺</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😡</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😮</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🤔</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">😴</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👍</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👎</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🙏</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👏</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🙌</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👋</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👌</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">✌️</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">❤️</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🔥</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">✨</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🎉</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">💯</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">📦</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">💬</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">❓</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">✅</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">❌</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">👕</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🧢</span>
                                        <span class="emoji-item" style="cursor:pointer; user-select:none; padding:4px; border-radius:6px; display:inline-block; transition:background 0.2s;">🥤</span>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="slack-send-btn" id="sendBtn">
                                <i class="bi bi-send-fill" style="font-size: 14px;"></i>
                            </button>
                        </div>
                    </form>
                @else
                    <div style="text-align:center; padding:12px; background:#1A1D21; border-radius:12px; color:#ef4444; font-size:13px; font-weight:500;">
                        ⚠️ Percakapan ini sudah ditutup. Aktifkan kembali di panel kanan untuk membalas.
                    </div>
                @endif
            </div>
        @else
            <div class="slack-chat-header">
                <div class="slack-chat-header-title">Detail Obrolan</div>
            </div>
            <div class="slack-messages-container" style="justify-content:center; align-items:center; text-align:center; min-height:300px;">
                <div style="font-size:48px; margin-bottom:16px;">💬</div>
                <h3 style="color:#ffffff; font-weight:700; margin-bottom:8px;">Selamat Datang di Customer Support</h3>
                <p style="color:#94a3b8; font-size:13px; max-width:320px; margin:0 auto; line-height:1.6;">
                    Pilih salah satu customer di panel kiri untuk mulai membaca dan membalas pesan obrolan secara interaktif.
                </p>
            </div>
        @endif
    </div>

    <!-- Right Pane: Context & Customer Details -->
    <div class="slack-details-pane">
        @if(isset($chat))
            <!-- Section: Product queried -->
            <div class="slack-details-section">
                <div class="slack-details-title">Produk Yang Ditanyakan</div>
                @if($chat->product)
                    @if($chat->product->main_photo)
                        @php
                            $photoUrl = '/storage/' . implode('/', array_map('rawurlencode', explode('/', $chat->product->main_photo)));
                        @endphp
                        <img src="{{ $photoUrl }}" style="width:100%; border-radius:10px; margin-bottom:12px; border:1px solid #2B2E33; background:#fff; display:block;" 
                             onerror="this.style.display='none'; document.getElementById('product-image-placeholder').style.display='flex';" />
                        <div id="product-image-placeholder" style="width:100%; height:160px; background:#2B2E33; border-radius:10px; margin-bottom:12px; display:none; align-items:center; justify-content:center; border:1px solid #2B2E33;">
                            <i class="bi bi-image" style="font-size:48px; color:#94a3b8;"></i>
                        </div>
                    @else
                        <div id="product-image-placeholder" style="width:100%; height:160px; background:#2B2E33; border-radius:10px; margin-bottom:12px; display:flex; align-items:center; justify-content:center; border:1px solid #2B2E33;">
                            <i class="bi bi-image" style="font-size:48px; color:#94a3b8;"></i>
                        </div>
                    @endif
                    <div style="font-weight:700; font-size:14.5px; color:#ffffff; line-height:1.4;">{{ $chat->product->name }}</div>
                    <div style="color:#3b82f6; font-weight:700; font-size:15px; margin-top:6px;">
                        Rp. {{ number_format($chat->product->price, 0, ',', '.') }}
                    </div>
                    <div style="display:inline-block; background:#2B2E33; color:#94a3b8; font-size:11px; padding:2px 8px; border-radius:4px; margin-top:10px; font-weight:500;">
                        Tanya Stok
                    </div>
                @elseif($chat->product_name)
                    <div style="font-weight:600; font-size:13.5px; color:#ffffff; line-height:1.4;">📦 {{ $chat->product_name }}</div>
                    <div style="display:inline-block; background:#2B2E33; color:#94a3b8; font-size:11px; padding:2px 8px; border-radius:4px; margin-top:8px;">
                        Konteks Manual
                    </div>
                @else
                    <div style="color:#94a3b8; font-size:13px; text-align:center; padding:16px 0;">
                        Tidak ada produk spesifik.
                    </div>
                @endif
            </div>

            <!-- Section: Customer Info -->
            <div class="slack-details-section">
                <div class="slack-details-title">Customer Info</div>
                <div style="display:flex; flex-direction:column; gap:8px; font-size:13px;">
                    <div>
                        <span style="color:#94a3b8;">Nama:</span>
                        <div style="font-weight:600; color:#fff; margin-top:2px;">{{ $chat->customer?->name ?? '-' }}</div>
                    </div>
                    <div>
                        <span style="color:#94a3b8;">Email:</span>
                        <div style="font-weight:600; color:#fff; margin-top:2px; word-break:break-all;">{{ $chat->customer?->email ?? '-' }}</div>
                    </div>
                    <div>
                        <span style="color:#94a3b8;">Daftar Akun:</span>
                        <div style="font-weight:600; color:#fff; margin-top:2px;">{{ $chat->customer?->created_at ? $chat->customer->created_at->format('d M Y') : '-' }}</div>
                    </div>
                </div>
            </div>

            <!-- Section: Actions -->
            <div class="slack-details-section">
                <div class="slack-details-title">Status Chat</div>
                @if($chat->status === 'open')
                    <form action="{{ route('admin.chats.close', $chat) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-danger" style="width:100%; font-size:13px; font-weight:700; border-radius:8px; padding:10px;"
                            onclick="return confirm('Tutup percakapan ini?')">
                            ✕ Tutup Chat
                        </button>
                    </form>
                @else
                    <form action="{{ route('admin.chats.reopen', $chat) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-primary" style="width:100%; font-size:13px; font-weight:700; border-radius:8px; padding:10px;">
                            ↩ Buka Kembali
                        </button>
                    </form>
                @endif
            </div>
        @else
            <div class="slack-details-section">
                <div class="slack-details-title">Detail Informasi</div>
                <div style="text-align:center; color:#94a3b8; font-size:13px; padding-top:40px;">
                    Tidak ada percakapan aktif.
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

// Handling Kirim Gambar (File Upload convert to Base64)
const paperclipBtn = document.getElementById('paperclipBtn');
const imageFileInput = document.getElementById('imageFileInput');
const replyForm = document.getElementById('replyForm');

if (paperclipBtn && imageFileInput) {
    paperclipBtn.addEventListener('click', () => {
        imageFileInput.click();
    });

    imageFileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (messageInput) {
                    messageInput.value = '[IMAGE]:' + e.target.result;
                    messageInput.removeAttribute('required');
                    replyForm?.requestSubmit();
                }
            };
            reader.readAsDataURL(file);
        }
    });
}

// Handling Sticker Popover Toggle
const stickerBtn = document.getElementById('stickerBtn');
const stickerPopover = document.getElementById('stickerPopover');

if (stickerBtn && stickerPopover) {
    stickerBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        stickerPopover.style.display = stickerPopover.style.display === 'none' ? 'block' : 'none';
    });

    // Close popover when clicking outside
    document.addEventListener('click', (e) => {
        if (stickerPopover && !stickerPopover.contains(e.target) && e.target !== stickerBtn) {
            stickerPopover.style.display = 'none';
        }
    });
}

// Handling Click on Emoji Item
document.querySelectorAll('.emoji-item').forEach(item => {
    item.addEventListener('click', function(e) {
        e.stopPropagation();
        const emoji = this.textContent;
        if (emoji && messageInput) {
            const startPos = messageInput.selectionStart;
            const endPos = messageInput.selectionEnd;
            const text = messageInput.value;
            messageInput.value = text.substring(0, startPos) + emoji + text.substring(endPos);
            const newCursorPos = startPos + emoji.length;
            messageInput.setSelectionRange(newCursorPos, newCursorPos);
            messageInput.focus();
        }
    });
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
            localStorage.setItem('hideDetailsPane', 'false');
        } else {
            detailsPane.style.display = 'none';
            localStorage.setItem('hideDetailsPane', 'true');
        }
        if (threeDotsDropdown) threeDotsDropdown.style.display = 'none';
    });

    // Restore state from localStorage
    const shouldHide = localStorage.getItem('hideDetailsPane') === 'true';
    if (shouldHide) {
        detailsPane.style.display = 'none';
    }
}

// Auto-refresh bubble setiap 5 detik (polling)
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
</script>
@endpush
@endsection
