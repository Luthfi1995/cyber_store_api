<header class="topbar">
    <div class="topbar-left">
        <button class="menu-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
            <!-- Menu Icon -->
            <svg id="menuIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-menu">
                <path d="M4 5h16" />
                <path d="M4 12h16" />
                <path d="M4 19h16" />
            </svg>
        </button>
        <div>
            <div class="breadcrumb" style="font-size: 11.5px; opacity: 0.85;">
                <a href="{{ route('admin.dashboard') }}">🏠 / Pages</a>
                @yield('breadcrumb')
            </div>
            <div class="page-title" style="margin-top: 1px;">@yield('page-title', 'Overview')</div>
        </div>
    </div>
    <div class="topbar-right" style="gap: 14px;">
        <!-- {{-- Search Input Pill --}}
        <div style="position: relative; display: flex; align-items: center;" class="d-none d-md-flex">
            <input type="text" placeholder="Type here..." style="padding: 7px 14px 7px 32px; font-size: 12.5px; border: 1px solid var(--border); border-radius: 20px; background: var(--bg-input); color: var(--text-primary); outline: none; width: 180px; transition: all 0.2s ease;">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="position: absolute; left: 12px; color: var(--text-muted);">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </div> -->

        @php
        $chatUnread = \App\Models\Chat::whereHas('messages', fn($q) =>
        $q->where('sender_type','customer')->where('is_read', false)
        )->count();
        @endphp
        <a href="{{ route('admin.chats.index') }}" class="theme-toggle-btn" title="Notifikasi Chat" style="position:relative; display:flex; align-items:center; justify-content:center; color:var(--text-primary); text-decoration:none;">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-bell">
                <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
            </svg>
            @if($chatUnread > 0)
            <span id="topbar-chat-badge" style="position:absolute; top:-4px; right:-4px; background:#DF0B2B; color:#fff; font-size:9px; font-weight:700; width:15px; height:15px; border-radius:50%; display:flex; align-items:center; justify-content:center; line-height:1;">
                {{ $chatUnread }}
            </span>
            @else
            <span id="topbar-chat-badge" style="position:absolute; top:-4px; right:-4px; background:#DF0B2B; color:#fff; font-size:9px; font-weight:700; width:15px; height:15px; border-radius:50%; display:none; align-items:center; justify-content:center; line-height:1;">
                0
            </span>
            @endif
        </a>

        <button id="themeToggle" class="theme-toggle-btn" title="Ganti Tema" onclick="toggleTheme()">
            <!-- Sun icon -->
            <svg id="themeIconSun" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
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
            <!-- Moon icon -->
            <svg id="themeIconMoon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                class="lucide lucide-moon" style="display: none;">
                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
            </svg>
        </button>

        <!-- User Dropdown Menu -->
        <div class="user-dropdown-container">
            <button type="button" class="user-dropdown-btn" onclick="toggleUserDropdown(event)" aria-expanded="false">
                <iconify-icon icon="lucide:user" style="font-size: 16px;"></iconify-icon>

                <iconify-icon icon="lucide:chevron-down" style="font-size: 14px; opacity: 0.7; transition: transform 0.2s;" id="userDropdownChevron"></iconify-icon>
            </button>

            <div id="userDropdownMenu" class="user-dropdown-menu">
                @if(auth()->check())
                <div class="user-dropdown-header">
                    <div class="user-dropdown-name">{{ auth()->user()->name }}</div>
                    <div class="user-dropdown-role">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</div>
                </div>

                <div class="user-dropdown-divider"></div>
                <button type="button" onclick="confirmLogout()" class="user-dropdown-item text-danger">
                    <iconify-icon icon="solar:logout-3-bold-duotone" style="font-size: 16px;"></iconify-icon>
                    <span>Keluar / Logout</span>
                </button>
                @else
                <a href="{{ route('admin.login') }}" class="user-dropdown-item">
                    <iconify-icon icon="solar:login-3-bold-duotone" style="font-size: 16px;"></iconify-icon>
                    <span>Sign In</span>
                </a>
                @endif
            </div>
        </div>
    </div>
</header>

<script>
    function toggleUserDropdown(e) {
        e.stopPropagation();
        const menu = document.getElementById('userDropdownMenu');
        const chevron = document.getElementById('userDropdownChevron');
        if (!menu) return;
        const isShown = menu.classList.contains('show');
        menu.classList.toggle('show');
        if (chevron) {
            chevron.style.transform = isShown ? 'rotate(0deg)' : 'rotate(180deg)';
        }
    }

    document.addEventListener('click', function(e) {
        const container = document.querySelector('.user-dropdown-container');
        const menu = document.getElementById('userDropdownMenu');
        const chevron = document.getElementById('userDropdownChevron');
        if (container && !container.contains(e.target) && menu && menu.classList.contains('show')) {
            menu.classList.remove('show');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    });
</script>