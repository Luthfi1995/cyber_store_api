<header class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
            <!-- Menu Icon -->
            <svg id="menuIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-menu">
                <path d="M4 5h16" />
                <path d="M4 12h16" />
                <path d="M4 19h16" />
            </svg>
            <!-- Close Icon -->
            <!-- <svg id="closeIcon" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-x" style="display: none;">
                <path d="M18 6 6 18" />
                <path d="m6 6 12 12" />
            </svg> -->
        </button>
        <div>
            <div class="page-title">@yield('page-title', 'Dashboard')</div>
            <div class="breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Home</a>
                @yield('breadcrumb')
            </div>
        </div>
    </div>
    <div class="topbar-right">
        @php
        $chatUnread = \App\Models\Chat::whereHas('messages', fn($q) =>
        $q->where('sender_type','customer')->where('is_read', false)
        )->count();
        @endphp
        <a href="{{ route('admin.chats.index') }}" class="theme-toggle-btn" title="Notifikasi Chat" style="position:relative; display:flex; align-items:center; justify-content:center; color:var(--text-primary); text-decoration:none; margin-right: 8px;">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
            </svg>
            @if($chatUnread > 0)
            <span id="topbar-chat-badge" style="position:absolute; top:-4px; right:-4px; background:#3C3565; color:#fff; font-size:9px; font-weight:700; width:15px; height:15px; border-radius:50%; display:flex; align-items:center; justify-content:center; line-height:1;">
                {{ $chatUnread }}
            </span>
            @else
            <span id="topbar-chat-badge" style="position:absolute; top:-4px; right:-4px; background:#3C3565; color:#fff; font-size:9px; font-weight:700; width:15px; height:15px; border-radius:50%; display:none; align-items:center; justify-content:center; line-height:1;">
                0
            </span>
            @endif
        </a>

        <button id="themeToggle" class="theme-toggle-btn" title="Ganti Tema" onclick="toggleTheme()">
            <!-- Sun icon for light mode (show when in dark mode to switch to light) -->
            <svg id="themeIconSun" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-sun" style="display: none;">
                <circle cx="12" cy="12" r="4" />
                <path d="M12 2v2" />
                <path d="M12 20v2" />
                <path d="m4.93 4.93 1.41 1.41" />
                <path d="m17.66 17.66 1.41 1.41" />
                <path d="M2 12h2" />
                <path d="M20 12h2" />
                <path d="m6.34 17.66-1.41 1.41" />
                <path d="m19.07 4.93-1.41 1.41" />
            </svg>
            <!-- Moon icon for dark mode (show when in light mode to switch to dark) -->
            <svg id="themeIconMoon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-moon" style="display: none;">
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
            </svg>
        </button>

        @php
        $role = auth()->user()->role;
        $rc = $role === 'superadmin' ? 'badge-superadmin' : ($role === 'admin' ? 'badge-admin' : 'badge-customer');
        @endphp
        <span class="badge {{ $rc }}"
            style="padding:4px 12px; font-size:11px; text-transform:uppercase; letter-spacing:.5px;">
            {{ ucfirst($role) }}
        </span>
    </div>
</header>