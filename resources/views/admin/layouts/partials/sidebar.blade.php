@use('App\Models\Chat')
@use('App\Models\Order')
@use('App\Models\Setting')
<!-- ─── SIDEBAR ─────────────────────────────────────────────────────── -->
<aside class="sidebar" id="sidebar">
    <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
        @php
        $sidebarLogoSetting = Setting::get('store_logo');
        $sidebarLogoUrl = $sidebarLogoSetting ? \Storage::disk('public')->url($sidebarLogoSetting) : asset('/assets/img/logo-cyberstore.jpg');
        @endphp
        <img src="{{ $sidebarLogoUrl }}" alt="Logo Toko" style="width: 36px; height: 36px; border-radius: 0.5rem; object-fit: cover; flex-shrink: 0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);">
        <div class="sidebar-brand-text">
            <div class="store-name">{{ Setting::get('store_name', 'BSI Cyber Store') }}</div>
            <div class="store-sub">{{ Setting::get('store_slogan', 'Your Trusted Cyber Store') }}</div>
        </div>
    </a>

    <nav class="sidebar-nav">
        {{-- Dashboard --}}
        <div class="nav-section-label">Utama</div>
        <a href="{{ route('admin.dashboard') }}"
            class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" data-tooltip="Dashboard">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-layout-dashboard">
                    <rect width="7" height="9" x="3" y="3" rx="1" />
                    <rect width="7" height="5" x="14" y="3" rx="1" />
                    <rect width="7" height="9" x="14" y="10" rx="1" />
                    <rect width="7" height="5" x="3" y="14" rx="1" />
                </svg>
            </span> Dashboard
        </a>

        {{-- Catalog --}}
        <div class="nav-section-label">Katalog</div>
        <a href="{{ route('admin.categories.index') }}"
            class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" data-tooltip="Kategori">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-tag">
                    <path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z" />
                    <path d="M6 6h.01" />
                </svg>
            </span> Kategori
        </a>
        <a href="{{ route('admin.products.index') }}"
            class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" data-tooltip="Produk">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-package">
                    <path d="m7.5 4.27 9 5.15" />
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z" />
                    <path d="m3.27 6.96 8.73 5 8.73-5" />
                    <path d="M12 22.08V12" />
                </svg>
            </span> Produk
        </a>
        <a href="{{ route('admin.stock-movements.index') }}"
            class="nav-link {{ request()->routeIs('admin.stock-movements.*') ? 'active' : '' }}" data-tooltip="Mutasi Stok">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trending-up">
                    <polyline points="22 7 13.5 15.5 8.5 10.5 2 17" />
                    <polyline points="16 7 22 7 22 13" />
                </svg>
            </span> Mutasi Stok
        </a>

        {{-- Transaksi --}}
        <div class="nav-section-label">Transaksi</div>
        <a href="{{ route('admin.orders.index') }}"
            class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" data-tooltip="Pesanan">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shopping-cart">
                    <circle cx="8" cy="21" r="1" />
                    <circle cx="19" cy="21" r="1" />
                    <path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12" />
                </svg>
            </span> Pesanan
            @php
            $pendingCancelRequests = Order::where('cancel_request_status', 'pending')->count();
            @endphp
            @if($pendingCancelRequests > 0)
            <span id="sidebar-cancel-badge" style="margin-left: auto; background-color: #f59e0b; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; display: inline-block; line-height: 1;" title="Pengajuan Pembatalan">
                {{ $pendingCancelRequests }} Batal
            </span>
            @endif
        </a>
        <a href="{{ route('admin.payments.index') }}"
            class="nav-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}" data-tooltip="Pembayaran">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-credit-card">
                    <rect width="20" height="14" x="2" y="5" rx="2" />
                    <line x1="2" x2="22" y1="10" y2="10" />
                </svg>
            </span> Pembayaran
        </a>
        <a href="{{ route('admin.chats.index') }}"
            class="nav-link {{ request()->routeIs('admin.chats.*') ? 'active' : '' }}" data-tooltip="Chat Customer">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
            </span> Chat Customer
            @php
            $chatUnread = Chat::whereHas('messages', fn($q) =>
            $q->where('sender_type','customer')->where('is_read', false)
            )->count();
            @endphp
            @if($chatUnread > 0)
            <span id="sidebar-chat-badge" style="margin-left: auto; background-color: #DF0B2B; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; display: inline-block; line-height: 1;">
                {{ $chatUnread }}
            </span>
            @else
            <span id="sidebar-chat-badge" style="margin-left: auto; background-color: #DF0B2B; color: white; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 10px; display: none; line-height: 1;">
                0
            </span>
            @endif
        </a>
        <a href="{{ route('admin.review-chats.index') }}"
            class="nav-link {{ request()->routeIs('admin.review-chats.*') ? 'active' : '' }}" data-tooltip="Chat Ulasan">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-circle">
                    <path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z" />
                </svg>
            </span> Chat Ulasan
        </a>
        <a href="{{ route('admin.announcements.index') }}"
            class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}" data-tooltip="Pengumuman">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </span> Pengumuman
        </a>

        {{-- Master Data --}}
        <div class="nav-section-label">Master Data</div>
        <a href="{{ route('admin.expeditions.index') }}"
            class="nav-link {{ request()->routeIs('admin.expeditions.*') ? 'active' : '' }}" data-tooltip="Ekspedisi">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-truck">
                    <rect x="1" y="3" width="15" height="13" rx="2" ry="2" />
                    <polygon points="16 8 20 8 23 11 23 16 16 16 16 8" />
                    <circle cx="5.5" cy="18.5" r="2.5" />
                    <circle cx="18.5" cy="18.5" r="2.5" />
                </svg>
            </span> Ekspedisi
        </a>

        {{-- Pengaturan --}}
        <div class="nav-section-label">Pengaturan</div>
        <a href="{{ route('admin.settings.index') }}"
            class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" data-tooltip="Pengaturan Toko">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-settings">
                    <circle cx="12" cy="12" r="3" />
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z" />
                </svg>
            </span> Pengaturan Toko
        </a>

        {{-- Banner Management --}}
        <div class="nav-section-label">Banner</div>
        <a href="{{ route('admin.banners.index') }}" class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}" data-tooltip="Banner">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-image">
                    <rect width="18" height="12" x="3" y="6" rx="2" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </span> Banner
        </a>

        {{-- User --}}
        <div class="nav-section-label">Pengguna</div>
        <a href="{{ route('admin.users.index') }}"
            class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" data-tooltip="Pengguna">
            <span class="icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-users">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M22 21v-2a4 4 0 0 1 0 7.75" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </span> Pengguna
        </a>


    </nav>

    {{-- User Card --}}
    <div class="sidebar-footer">
        <div class="user-card">
            <div class="user-avatar">
                @if (auth()->user()->photo)
                <img src="{{ \Storage::disk('public')->url(auth()->user()->photo) }}"
                    alt="{{ auth()->user()->name }}">
                @else
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </div>
            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
            </div>
            <button type="button" class="logout-btn" title="Logout" onclick="confirmLogout()">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" class="lucide lucide-power">
                    <path d="M12 2v10" />
                    <path d="M18.4 6.6a9 9 0 1 1-12.77.04" />
                </svg>
            </button>
        </div>
    </div>
</aside>