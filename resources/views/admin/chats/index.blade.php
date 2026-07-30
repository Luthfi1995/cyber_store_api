@extends('admin.layouts.app')
@section('title', isset($chat) ? 'Chat dengan ' . $chat->customer?->name : 'Chat Customer')
@section('page-title', 'Support Chat')
@section('breadcrumb')
<span class="breadcrumb-sep">›</span>
<span>Support Chat</span>
@endsection

@push('styles')
<style>
    /* ── Slack Workspace Style (Theme Adaptive to Screenshot) ── */
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
        align-items: center;
        gap: 12px;
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
        color: #D3D3D3;
    }

    .slack-chat-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #ffd8a8;
        color: #e8590c;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        flex-shrink: 0;
    }

    .slack-chat-item.active .slack-chat-avatar {
        background: #ffffff;
        color: #0F62FE;
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
        color: #1e293b;
    }

    .slack-chat-item.active .slack-chat-name-row {
        color: #ffffff;
    }

    .slack-chat-message {
        font-size: 12px;
        color: #64748b;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        margin-top: 3px;
    }

    .slack-chat-item.active .slack-chat-message {
        color: rgba(255, 255, 255, 0.8);
    }

    .slack-chat-badge {
        background: #0F62FE;
        color: #ffffff;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 999px;
        margin-left: 6px;
        flex-shrink: 0;
    }

    .slack-chat-item.active .slack-chat-badge {
        background: #ffffff;
        color: #0F62FE;
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
        background: #D3D3D3;
    }

    /* Chat bubble styling */
    .slack-msg-row {
        display: flex;
        gap: 12px;
        align-items: flex-start;
    }

    .slack-msg-row.customer {
        flex-direction: row;
    }

    .slack-msg-row.admin {
        flex-direction: row-reverse;
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
        max-width: 70%;
        display: flex;
        flex-direction: column;
    }

    .slack-msg-row.customer .slack-msg-body {
        align-items: flex-start;
    }

    .slack-msg-row.admin .slack-msg-body {
        align-items: flex-end;
    }

    .slack-msg-bubble {
        padding: 12px 16px;
        border-radius: 14px;
        font-size: 14px;
        line-height: 1.5;
        word-break: break-word;
    }

    .slack-msg-row.customer .slack-msg-bubble {
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #cce0ff;
        border-top-left-radius: 2px;
    }

    .slack-msg-row.admin .slack-msg-bubble {
        background: #0F62FE;
        color: #ffffff;
        border: none;
        border-top-right-radius: 2px;
    }

    .no-bubble-style {
        background: transparent !important;
        box-shadow: none !important;
        padding: 0 !important;
        border: none !important;
    }

    .slack-msg-time {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 4px;
        padding: 0 4px;
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

    .slack-input-icon {
        color: #64748b;
        font-size: 18px;
        cursor: pointer;
        transition: color 0.2s;
        display: flex;
        align-items: center;
    }

    .emoji-item:hover {
        background: #f1f5f9;
    }

    #threeDotsDropdown a:hover {
        background: #f1f5f9 !important;
        color: #1e293b !important;
    }

    .slack-input-icon:hover {
        color: #1e293b;
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
    [data-theme="dark"] .slack-chat-item.active .slack-chat-avatar {
        background: #ffffff;
        color: #0F62FE;
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
    [data-theme="dark"] .slack-msg-avatar {
        background: #3d4350;
        color: #cbd5e1;
    }
    [data-theme="dark"] .slack-msg-row.customer .slack-msg-bubble {
        background: #3d4350;
        color: #f1f5f9;
        border: 1px solid #4d5566;
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
    [data-theme="dark"] .slack-input-icon {
        color: #94a3b8;
    }
    [data-theme="dark"] .slack-input-icon:hover {
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
    [data-theme="dark"] #product-image-placeholder {
        background: #3d4350 !important;
        border-color: #4d5566 !important;
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
    [data-theme="dark"] #threeDotsDropdown,
    [data-theme="dark"] #stickerPopover {
        background: #1A1D21 !important;
        border-color: #3d4350 !important;
        box-shadow: 0 8px 20px rgba(0,0,0,0.5) !important;
    }
    [data-theme="dark"] #threeDotsDropdown a {
        color: #ffffff !important;
    }
    [data-theme="dark"] #threeDotsDropdown a:hover {
        background: #3d4350 !important;
    }
    [data-theme="dark"] .emoji-item:hover {
        background: #3d4350 !important;
    }
    [data-theme="dark"] .slack-messages-container h3 {
        color: #ffffff !important;
    }
    @media (max-width: 768px) {
        .slack-chat-workspace {
            flex-direction: column !important;
            height: auto !important;
            padding: 8px !important;
        }
        .slack-sidebar {
            width: 100% !important;
            max-height: 250px !important;
        }
        .slack-main-chat {
            width: 100% !important;
            min-height: 400px !important;
        }
        .slack-details-pane {
            width: 100% !important;
        }
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
            <form method="GET" action="{{ route('admin.chats.index') }}" style="display: flex; gap: 6px;">
                <input type="text" name="search" class="slack-search-input"
                    placeholder="🔍 Cari customer..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm" style="background-color: #3C3565; color: #ffffff; border: none; padding: 0 12px; border-radius: 8px; font-weight: 600; flex-shrink: 0; display: flex; align-items: center; justify-content: center; gap: 4px;" title="Cari Customer">
                    <iconify-icon icon="lucide:search" style="font-size: 14px;"></iconify-icon>
                    Cari
                </button>
                @if(request('search'))
                <a href="{{ route('admin.chats.index') }}" class="btn btn-sm" style="background: var(--bg-card, #ffffff); color: var(--text-primary, #1e293b); border: 1px solid var(--border, #cbd5e1); border-radius: 8px; padding: 0 10px; display: flex; align-items: center; justify-content: center; font-weight: 600;" title="Reset Search">
                    <iconify-icon icon="lucide:x" style="font-size: 15px; color: var(--text-primary, #1e293b);"></iconify-icon>
                </a>
                @endif
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
                        @if(Str::startsWith($c->lastMessage?->message ?? '', '[IMAGE]:') || Str::contains($c->lastMessage?->message ?? '', 'data:image'))
                        📷 [Gambar]
                        @else
                        {{ $c->lastMessage?->message ?? 'Memulai percakapan...' }}
                        @endif
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
                <i class="bi bi-three-dots-vertical" id="threeDotsBtn" style="color: #64748b; font-size: 18px; cursor: pointer; padding: 4px;"></i>

                {{-- Dropdown Menu --}}
                <div id="threeDotsDropdown" style="display:none; position:absolute; right:0; top:30px; background:#ffffff; border:1px solid #e2e8f0; border-radius:8px; width:160px; box-shadow:0 8px 20px rgba(0,0,0,0.08); z-index:1050; padding:4px 0;">
                    <a href="javascript:void(0);" id="toggleDetailsBtn" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#1e293b; font-size:13px; text-decoration:none; transition:background 0.2s;">
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
                    <a href="javascript:void(0);" onclick="document.getElementById('dropdownReopenForm').submit();" style="display:flex; align-items:center; gap:8px; padding:10px 16px; color:#0F62FE; font-size:13px; text-decoration:none; transition:background 0.2s;">
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
            $msgContent = '<img src="' . $base64 . '" style="max-width:260px; border-radius:10px; display:block; cursor:pointer;" onclick="openImageModal(this.src)" title="Klik untuk lihat gambar penuh" />';
            } elseif (str_starts_with($msgText, '[STICKER]:')) {
            $stickerUrl = substr($msgText, 10);
            $msgContent = '<img src="' . e($stickerUrl) . '" style="width:100px; height:100px; display:block;" />';
            $bubbleStyle = 'background:transparent; box-shadow:none; padding:0;';
            } elseif (str_starts_with($msgText, '[VOICE]:')) {
            $duration = substr($msgText, 8);
            $padDuration = str_pad($duration, 2, '0', STR_PAD_LEFT);
            $msgContent = '
            <div style="display:flex; align-items:center; gap:10px; padding:4px 0; min-width:180px;">
                <button type="button" style="background:rgba(255,255,255,0.15); border:none; border-radius:50%; width:32px; height:32px; color:white; display:flex; align-items:center; justify-content:center; cursor:pointer;" onclick="playMockAudio(this, ' . e($duration) . ')">
                    <i class="bi bi-play-fill" style="font-size:16px;"></i>
                </button>
                <div style="flex:1;">
                    <div style="height:4px; background:rgba(255,255,255,0.2); border-radius:2px; overflow:hidden;">
                        <div class="progress-bar-fill" style="width:0%; height:100%; background:#fff; transition:width 0.1s linear;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:10px; color:rgba(255,255,255,0.7); margin-top:4px;">
                        <span class="audio-time">0:00</span>
                        <span>0:' . $padDuration . '</span>
                    </div>
                </div>
            </div>';
            } else {
            $msgContent = e($msgText);
            }
            @endphp

            <div class="slack-msg-row {{ $msg->sender_type }}">
                <div class="slack-msg-avatar">{{ $avatarText }}</div>
                <div class="slack-msg-body">
                    <div class="slack-msg-bubble {{ !empty($bubbleStyle) ? 'no-bubble-style' : '' }}">
                        {!! $msgContent !!}
                    </div>
                    <div class="slack-msg-time" style="display: flex; align-items: center; gap: 4px;">
                        {{ $msg->created_at->format('H:i') }}
                        @if($isSelf)
                        @if($msg->is_read)
                        <i class="bi bi-check-all" style="color: #3b82f6; font-size: 14px; line-height: 1;" title="Dibaca"></i>
                        @else
                        <i class="bi bi-check-all" style="color: #64748b; font-size: 14px; line-height: 1;" title="Terkirim"></i>
                        @endif
                        @endif
                    </div>
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
                        <div id="stickerPopover" style="display:none; position:absolute; bottom:40px; right:0; background:#ffffff; border:1px solid #e2e8f0; border-radius:12px; padding:12px; width:280px; box-shadow:0 10px 25px rgba(0,0,0,0.08); z-index:1000;">
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
            <div style="text-align:center; padding:12px; background:#fee2e2; border:1px solid #fecaca; border-radius:12px; color:#ef4444; font-size:13px; font-weight:500;">
                ⚠️ Percakapan ini sudah ditutup. Buka kembali untuk membalas.
            </div>
            @endif
        </div>
        @else
        <div class="slack-chat-header">
            <div class="slack-chat-header-title">Detail Obrolan</div>
        </div>
        <div class="slack-messages-container" style="justify-content:center; align-items:center; text-align:center; min-height:300px;">
            <div style="font-size:48px; margin-bottom:16px;">💬</div>
            <h3 style="color:#1e293b; font-weight:700; margin-bottom:8px;">Selamat Datang di Customer Support</h3>
            <p style="color:#64748b; font-size:13px; max-width:320px; margin:0 auto; line-height:1.6;">
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
            <img src="{{ $photoUrl }}" style="width:100%; border-radius:10px; margin-bottom:12px; border:1px solid #e2e8f0; background:#fff; display:block;"
                onerror="this.style.display='none'; document.getElementById('product-image-placeholder').style.display='flex';" />
            <div id="product-image-placeholder" style="width:100%; height:160px; background:#f1f5f9; border-radius:10px; margin-bottom:12px; display:none; align-items:center; justify-content:center; border:1px solid #e2e8f0;">
                <i class="bi bi-image" style="font-size:48px; color:#94a3b8;"></i>
            </div>
            @else
            <div id="product-image-placeholder" style="width:100%; height:160px; background:#f1f5f9; border-radius:10px; margin-bottom:12px; display:flex; align-items:center; justify-content:center; border:1px solid #e2e8f0;">
                <i class="bi bi-image" style="font-size:48px; color:#94a3b8;"></i>
            </div>
            @endif
            <div style="font-weight:700; font-size:14.5px; color:#1e293b; line-height:1.4;">{{ $chat->product->name }}</div>
            <div style="color:#0F62FE; font-weight:700; font-size:15px; margin-top:6px;">
                Rp. {{ number_format($chat->product->price, 0, ',', '.') }}
            </div>
            <div style="display:inline-block; background:#e2e8f0; color:#475569; font-size:11px; padding:2px 8px; border-radius:4px; margin-top:10px; font-weight:500;">
                Tanya Stok
            </div>
            @elseif($chat->product_name)
            <div style="font-weight:600; font-size:13.5px; color:#1e293b; line-height:1.4;">📦 {{ $chat->product_name }}</div>
            <div style="display:inline-block; background:#e2e8f0; color:#475569; font-size:11px; padding:2px 8px; border-radius:4px; margin-top:8px;">
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
                    <div style="font-weight:600; color:#1e293b; margin-top:2px;">{{ $chat->customer?->name ?? '-' }}</div>
                </div>
                <div>
                    <span style="color:#94a3b8;">Email:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px; word-break:break-all;">{{ $chat->customer?->email ?? '-' }}</div>
                </div>
                <div>
                    <span style="color:#94a3b8;">Daftar Akun:</span>
                    <div style="font-weight:600; color:#1e293b; margin-top:2px;">{{ $chat->customer?->created_at ? $chat->customer->created_at->format('d M Y') : '-' }}</div>
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
    setInterval(function() {
        fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.text())
            .then(html => {
                const parser = new DOMParser();
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

    function openImageModal(src) {
        const modal = document.getElementById('imagePreviewModal');
        const img = document.getElementById('imagePreviewModalImg');
        if (modal && img) {
            img.src = src;
            modal.style.display = 'flex';
        }
    }

    function closeImageModal() {
        const modal = document.getElementById('imagePreviewModal');
        if (modal) modal.style.display = 'none';
    }
</script>

<!-- Modal Image Preview -->
<div id="imagePreviewModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.85); z-index:99999; align-items:center; justify-content:center; backdrop-filter:blur(6px);" onclick="closeImageModal()">
    <img id="imagePreviewModalImg" src="" style="max-width:90vw; max-height:90vh; border-radius:12px; box-shadow:0 20px 40px rgba(0,0,0,0.5); object-fit:contain;">
    <button type="button" style="position:absolute; top:20px; right:20px; background:rgba(255,255,255,0.2); border:none; color:white; font-size:24px; border-radius:50%; width:44px; height:44px; cursor:pointer; display:flex; align-items:center; justify-content:center;" onclick="closeImageModal()">&times;</button>
</div>
@endpush
@endsection