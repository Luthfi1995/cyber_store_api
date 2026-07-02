<script>
    function toggleSidebar() {
        if (window.innerWidth > 768) {
            document.body.classList.toggle('sidebar-collapsed');
        } else {
            document.getElementById('sidebar').classList.toggle('open');
            document.getElementById('sidebarOverlay').classList.toggle('open');
        }
        updateSidebarToggleIcon();
    }

    function closeSidebar() {
        if (window.innerWidth > 768) {
            document.body.classList.add('sidebar-collapsed');
        } else {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('sidebarOverlay').classList.remove('open');
        }
        updateSidebarToggleIcon();
    }

    function confirmDelete(url, name) {
        document.getElementById('confirmModalBody').textContent =
            `Apakah Anda yakin ingin menghapus "${name}"? Tindakan ini tidak dapat dibatalkan.`;
        document.getElementById('confirmForm').action = url;
        document.getElementById('confirmModal').classList.add('open');
    }

    function closeConfirm() {
        document.getElementById('confirmModal').classList.remove('open');
    }

    function confirmLogout() {
        document.getElementById('logoutModal').classList.add('open');
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').classList.remove('open');
    }
    // Preview avatar upload
    function previewAvatar(input) {
        if (input.files && input.files[0]) {
            const reader = new window.FileReader();
            reader.onload = e => {
                const prev = document.getElementById('avatarPreview');
                if (prev) prev.src = e.target.result;
                const wrap = document.getElementById('avatarPreviewWrap');
                if (wrap) wrap.innerHTML =
                    `<img id="avatarPreview" src="${e.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
            };
            reader.readAsDataURL(input.files[0]);
        }
    }
    // Auto-hide alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(a => {
            a.style.transition = 'opacity .5s';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 500);
        });
    }, 4500);

    // Theme toggle helper functions
    function updateThemeIcons() {
        const theme = document.documentElement.getAttribute('data-theme') || 'dark';
        const sunIcon = document.getElementById('themeIconSun');
        const moonIcon = document.getElementById('themeIconMoon');
        if (sunIcon && moonIcon) {
            if (theme === 'light') {
                sunIcon.style.display = 'none';
                moonIcon.style.display = 'block';
            } else {
                sunIcon.style.display = 'block';
                moonIcon.style.display = 'none';
            }
        }
    }

    function toggleTheme() {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'dark';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', newTheme);
        localStorage.setItem('theme', newTheme);
        updateThemeIcons();
    }

    // Sidebar toggle icon helper
    function updateSidebarToggleIcon() {
        const menuIcon = document.getElementById('menuIcon');
        const closeIcon = document.getElementById('closeIcon');
        if (menuIcon && closeIcon) {
            let isCollapsed = false;
            if (window.innerWidth > 768) {
                isCollapsed = document.body.classList.contains('sidebar-collapsed');
            } else {
                const sidebar = document.getElementById('sidebar');
                isCollapsed = sidebar ? !sidebar.classList.contains('open') : true;
            }

            if (isCollapsed) {
                menuIcon.style.display = 'block';
                closeIcon.style.display = 'none';
            } else {
                menuIcon.style.display = 'none';
                closeIcon.style.display = 'block';
            }
        }
    }

    // Initialize icons
    updateThemeIcons();
    updateSidebarToggleIcon();
    window.addEventListener('resize', updateSidebarToggleIcon);
</script>

@auth
<script>
    (function() {
        let lastUnreadCount = parseInt(document.getElementById('sidebar-chat-badge')?.innerText || '0');

        let audioCtx = null;
        function initAudioContext() {
            if (!audioCtx) {
                audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
            if (audioCtx && audioCtx.state === 'suspended') {
                audioCtx.resume();
            }
        }
        document.addEventListener('click', initAudioContext, { once: true });
        document.addEventListener('keydown', initAudioContext, { once: true });

        function playNotificationSound() {
            try {
                initAudioContext();
                if (!audioCtx) return;
                
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.type = 'sine';
                
                // Ring/alert melody pattern: D5 -> A5 -> F5
                const now = audioCtx.currentTime;
                osc.frequency.setValueAtTime(587.33, now); // D5
                osc.frequency.setValueAtTime(880, now + 0.12); // A5
                osc.frequency.setValueAtTime(698.46, now + 0.24); // F5
                
                gain.gain.setValueAtTime(0.12, now);
                gain.gain.exponentialRampToValueAtTime(0.01, now + 0.45);
                
                osc.start(now);
                osc.stop(now + 0.45);
            } catch (e) {
                console.warn("AudioContext failed:", e);
            }
        }

        function showToastNotification() {
            // Remove existing toast if any
            const existing = document.getElementById('chatToastNotification');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.id = 'chatToastNotification';
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: -350px;
                background: var(--bg-card, #ffffff);
                border-left: 4px solid var(--accent, #4f6ef7);
                border-radius: var(--radius, 12px);
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.25);
                padding: 16px 20px;
                z-index: 9999;
                transition: right 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                display: flex;
                align-items: center;
                gap: 12px;
                font-family: 'Inter', sans-serif;
                cursor: pointer;
                border: 1px solid var(--border);
            `;
            toast.innerHTML = `
                <div style="font-size: 24px;">💬</div>
                <div>
                    <div style="font-weight: 700; font-size: 14px; color: var(--text-primary, #0f172a); margin-bottom: 2px;">Chat Baru Masuk!</div>
                    <div style="font-size: 12px; color: var(--text-secondary, #475569);">Customer membutuhkan bantuan. Klik untuk balas.</div>
                </div>
            `;
            toast.onclick = () => {
                window.location.href = "{{ route('admin.chats.index') }}";
            };
            document.body.appendChild(toast);
            setTimeout(() => toast.style.right = '20px', 100);

            // Play Sound
            playNotificationSound();

            // Auto remove
            setTimeout(() => {
                toast.style.right = '-350px';
                setTimeout(() => toast.remove(), 500);
            }, 6000);
        }

        function checkUnreadChats() {
            fetch("{{ route('admin.chats.unread-count') }}")
                .then(res => res.json())
                .then(data => {
                    const count = parseInt(data.unread || '0');
                    if (count > lastUnreadCount) {
                        showToastNotification();
                        
                        // Shake/wiggle the floating chat bubble
                        const bubble = document.getElementById('floating-chat-bubble');
                        if (bubble) {
                            bubble.classList.add('wiggle-animation');
                            setTimeout(() => bubble.classList.remove('wiggle-animation'), 600);
                        }
                    }

                    // Update sidebar badges
                    const badge = document.getElementById('sidebar-chat-badge');
                    if (badge) {
                        badge.innerText = count;
                        badge.style.display = count > 0 ? 'block' : 'none';
                    }

                    // Update topbar badges
                    const topbarBadge = document.getElementById('topbar-chat-badge');
                    if (topbarBadge) {
                        topbarBadge.innerText = count;
                        topbarBadge.style.display = count > 0 ? 'flex' : 'none';
                    }

                    // Update bubble badge
                    const bubbleBadge = document.getElementById('bubble-unread-badge');
                    if (bubbleBadge) {
                        bubbleBadge.innerText = count;
                        bubbleBadge.style.display = count > 0 ? 'flex' : 'none';
                    }

                    lastUnreadCount = count;
                })
                .catch(() => {});
        }

        // Poll every 10 seconds
        setInterval(checkUnreadChats, 10000);

        // ── FLOATING CHAT WIDGET ──────────────────────────────────────────
        let activeChatId = null;
        let widgetPollInterval = null;

        // Styles injection
        const style = document.createElement('style');
        style.textContent = `
            #floating-chat-bubble {
                position: fixed; bottom: 20px; right: 20px; z-index: 9998;
                width: 56px; height: 56px; border-radius: 50%;
                background: var(--accent, #4f6ef7); display: flex; align-items: center; justify-content: center;
                box-shadow: 0 4px 12px rgba(0,0,0,0.25); cursor: pointer; color: white;
                transition: transform 0.2s, background 0.2s;
            }
            #floating-chat-bubble:hover { transform: scale(1.05); background: var(--accent-hover, #3b5cf6); }
            
            @keyframes wiggle-chat {
                0% { transform: scale(1) rotate(0deg); }
                15% { transform: scale(1.1) rotate(8deg); }
                30% { transform: scale(1.1) rotate(-8deg); }
                45% { transform: scale(1.1) rotate(6deg); }
                60% { transform: scale(1.1) rotate(-6deg); }
                75% { transform: scale(1.1) rotate(3deg); }
                90% { transform: scale(1.1) rotate(-3deg); }
                100% { transform: scale(1) rotate(0deg); }
            }
            .wiggle-animation {
                animation: wiggle-chat 0.6s ease-in-out;
            }
            
            #floating-chat-window {
                position: fixed; bottom: 90px; right: 20px; z-index: 9998;
                width: 360px; height: 480px; display: none; flex-direction: column;
                background: var(--bg-card, #ffffff); border: 1px solid var(--border);
                border-radius: 16px; box-shadow: 0 8px 30px rgba(0,0,0,0.2);
                overflow: hidden; font-family: 'Inter', sans-serif;
            }
            #floating-chat-window.open { display: flex; animation: slideUp-chat 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.2); }
            @keyframes slideUp-chat { from { opacity: 0; transform: translateY(20px) scale(0.95); } to { opacity: 1; transform: translateY(0) scale(1); } }
            
            .widget-header {
                background: var(--accent, #4f6ef7); color: white; padding: 14px 16px;
                display: flex; align-items: center; justify-content: space-between;
                flex-shrink: 0;
            }
            .widget-header-title { font-weight: 700; font-size: 14px; display: flex; align-items: center; gap: 8px; }
            .widget-header-actions { display: flex; gap: 8px; align-items: center; }
            .widget-btn { background: none; border: none; color: white; cursor: pointer; padding: 4px; display: flex; align-items: center; transition: opacity 0.2s; }
            .widget-btn:hover { opacity: 0.8; }
            
            .widget-body { flex: 1; overflow-y: auto; display: flex; flex-direction: column; background: var(--bg-dark, #f8fafc); }
            
            /* Chat list */
            .widget-chat-item {
                display: flex; gap: 10px; padding: 12px 16px; border-bottom: 1px solid var(--border);
                cursor: pointer; transition: background 0.2s; align-items: center;
            }
            .widget-chat-item:hover { background: var(--bg-card-hover, rgba(0,0,0,0.02)); }
            .widget-chat-avatar {
                width: 36px; height: 36px; border-radius: 50%; background: #64748b; color: white;
                display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0;
            }
            .widget-chat-details { flex: 1; min-width: 0; }
            .widget-chat-name { font-weight: 700; font-size: 13px; color: var(--text-primary); margin-bottom: 2px; }
            .widget-chat-message { font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .widget-chat-badge {
                background: #ef4444; color: white; font-size: 9px; font-weight: 700;
                padding: 1px 6px; border-radius: 999px; min-width: 15px; text-align: center;
            }
            
            /* Conversations */
            .widget-messages-container { display: flex; flex-direction: column; gap: 10px; padding: 14px; flex: 1; overflow-y: auto; }
            .widget-msg-wrap { display: flex; gap: 8px; align-items: flex-end; }
            .widget-msg-wrap.admin { flex-direction: row-reverse; }
            .widget-msg-avatar {
                width: 24px; height: 24px; border-radius: 50%; background: var(--accent, #4f6ef7); color: white;
                display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; flex-shrink: 0;
            }
            .widget-msg-wrap.customer .widget-msg-avatar { background: #64748b; }
            .widget-msg-bubble {
                max-width: 75%; padding: 8px 12px; border-radius: 12px; font-size: 12.5px; line-height: 1.4; word-break: break-word;
            }
            .widget-msg-bubble.customer { background: var(--bg-card, #fff); border: 1px solid var(--border); border-bottom-left-radius: 2px; color: var(--text-primary); }
            .widget-msg-bubble.admin { background: var(--accent, #4f6ef7); color: white; border-bottom-right-radius: 2px; }
            .widget-msg-time { font-size: 9px; color: var(--text-muted); margin-top: 2px; text-align: right; }
            .widget-msg-wrap.customer .widget-msg-time { text-align: left; }
            
            /* Footer reply form */
            .widget-footer { padding: 10px; background: var(--bg-card, #fff); border-top: 1px solid var(--border); display: flex; gap: 8px; align-items: center; flex-shrink: 0; }
            .widget-input {
                flex: 1; border: 1px solid var(--border); background: var(--bg-input, #f1f5f9); color: var(--text-primary);
                border-radius: 20px; padding: 8px 14px; font-size: 12.5px; outline: none; transition: border-color 0.2s;
            }
            .widget-input:focus { border-color: var(--accent); }
            .widget-send-btn {
                background: var(--accent); border: none; border-radius: 50%; width: 32px; height: 32px;
                color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;
            }
            .widget-send-btn:hover { background: var(--accent-hover); }
        `;
        document.head.appendChild(style);

        // Render Bubble Button
        const chatBubble = document.createElement('div');
        chatBubble.id = 'floating-chat-bubble';
        chatBubble.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-message-square"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            <span id="bubble-unread-badge" style="position:absolute; top:-2px; right:-2px; background:#ef4444; color:#fff; font-size:9px; font-weight:700; width:18px; height:18px; border-radius:50%; display:none; align-items:center; justify-content:center; border:2px solid var(--bg-dark);">0</span>
        `;
        document.body.appendChild(chatBubble);

        // Render Chat Window
        const chatBox = document.createElement('div');
        chatBox.id = 'floating-chat-window';
        document.body.appendChild(chatBox);

        // Open/Close toggle
        chatBubble.onclick = () => {
            chatBox.classList.toggle('open');
            if (chatBox.classList.contains('open')) {
                openChatList();
            } else {
                closeWidgetConversation();
            }
        };

        function openChatList() {
            activeChatId = null;
            closeWidgetConversation();
            chatBox.innerHTML = `
                <div class="widget-header">
                    <span class="widget-header-title">💬 Chat Support</span>
                    <button class="widget-btn" onclick="document.getElementById('floating-chat-window').classList.remove('open')">✕</button>
                </div>
                <div class="widget-body" id="widgetBody">
                    <div style="display:flex; justify-content:center; padding:40px;"><div class="loading-spinner">Mencari chat...</div></div>
                </div>
            `;
            loadWidgetChats();
        }

        function loadWidgetChats() {
            fetch("{{ route('admin.chats.index') }}", { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    const container = document.getElementById('widgetBody');
                    if (!container) return;
                    
                    if (!data.chats || data.chats.length === 0) {
                        container.innerHTML = `<div style="text-align:center; color:var(--text-muted); padding:40px; font-size:13px;">Belum ada chat masuk.</div>`;
                        return;
                    }

                    let html = '';
                    data.chats.forEach(chat => {
                        const name = chat.customer?.name || 'Customer';
                        const initials = name.substring(0, 1).toUpperCase();
                        const unreadHtml = chat.unread_count > 0 ? `<span class="widget-chat-badge">${chat.unread_count}</span>` : '';
                        const lastMsg = chat.last_message?.message || 'Memulai percakapan...';
                        const productHtml = chat.product_name ? `<div style="font-size:11px; color:var(--text-muted); margin-top:2px; display:flex; align-items:center; gap:4px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">📦 ${chat.product_name}</div>` : '';

                        html += `
                            <div class="widget-chat-item" onclick="openWidgetChat(${chat.id}, '${name}', '${chat.product_name || ''}')">
                                <div class="widget-chat-avatar">${initials}</div>
                                <div class="widget-chat-details" style="min-width:0;">
                                    <div class="widget-chat-name">${name}</div>
                                    ${productHtml}
                                    <div class="widget-chat-message" style="margin-top:2px;">${lastMsg}</div>
                                </div>
                                ${unreadHtml}
                            </div>
                        `;
                    });
                    container.innerHTML = html;
                })
                .catch(() => {
                    const container = document.getElementById('widgetBody');
                    if (container) container.innerHTML = `<div style="padding:20px; font-size:12px; color:red;">Gagal memuat chat.</div>`;
                });
        }

        window.openWidgetChat = function(chatId, customerName, productName) {
            activeChatId = chatId;
            const productBar = productName && productName !== 'null' && productName !== '' ? `
                <div style="background:var(--bg-card-hover, rgba(0,0,0,0.03)); padding:8px 16px; border-bottom:1px solid var(--border); font-size:11px; color:var(--text-muted); display:flex; align-items:center; gap:4px; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                    📦 Produk: <strong style="color:var(--text-primary);">${productName}</strong>
                </div>
            ` : '';

            chatBox.innerHTML = `
                <div class="widget-header">
                    <div class="widget-header-title">
                        <button class="widget-btn" onclick="openChatList()">←</button>
                        <span>${customerName}</span>
                    </div>
                    <button class="widget-btn" onclick="document.getElementById('floating-chat-window').classList.remove('open'); closeWidgetConversation();">✕</button>
                </div>
                ${productBar}
                <div class="widget-body">
                    <div class="widget-messages-container" id="widgetMessages"></div>
                    <div class="widget-footer">
                        <input type="text" class="widget-input" id="widgetInput" placeholder="Ketik balasan..." onkeydown="if(event.key === 'Enter') sendWidgetReply()">
                        <button class="widget-send-btn" onclick="sendWidgetReply()">↗</button>
                    </div>
                </div>
            `;
            loadWidgetConversation();
            widgetPollInterval = setInterval(loadWidgetConversation, 4000);
        };

        function closeWidgetConversation() {
            if (widgetPollInterval) {
                clearInterval(widgetPollInterval);
                widgetPollInterval = null;
            }
        }

        function loadWidgetConversation() {
            if (!activeChatId) return;
            fetch(`/admin/chats/${activeChatId}`, { headers: { 'Accept': 'application/json' } })
                .then(res => res.json())
                .then(data => {
                    const msgContainer = document.getElementById('widgetMessages');
                    if (!msgContainer) return;
                    
                    const wasAtBottom = msgContainer.scrollTop + msgContainer.clientHeight >= msgContainer.scrollHeight - 10;
                    
                    let html = '';
                    data.messages.forEach(msg => {
                        const isSelf = msg.sender_type === 'admin';
                        const avatarChar = isSelf ? 'A' : data.chat.customer?.name?.substring(0, 1).toUpperCase() || 'C';
                        const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                        let msgContent = msg.message;
                        let bubbleStyle = '';
                        if (msgContent.indexOf('[IMAGE]:') === 0) {
                            const base64 = msgContent.substring(8);
                            msgContent = `<img src="${base64}" style="max-width:180px; border-radius:8px; display:block;" />`;
                        } else if (msgContent.indexOf('[STICKER]:') === 0) {
                            const stickerUrl = msgContent.substring(10);
                            msgContent = `<img src="${stickerUrl}" style="width:70px; height:70px; display:block;" />`;
                            bubbleStyle = 'background:transparent;box-shadow:none;padding:0;';
                        } else {
                            msgContent = msgContent.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
                        }

                        html += `
                            <div class="widget-msg-wrap ${msg.sender_type}">
                                <div class="widget-msg-avatar">${avatarChar}</div>
                                <div class="widget-msg-bubble ${msg.sender_type}" style="${bubbleStyle}">
                                    <div>${msgContent}</div>
                                    <div class="widget-msg-time">${time}</div>
                                </div>
                            </div>
                        `;
                    });
                    
                    msgContainer.innerHTML = html;
                    if (wasAtBottom || msgContainer.scrollTop === 0) {
                        msgContainer.scrollTop = msgContainer.scrollHeight;
                    }
                });
        }

        window.sendWidgetReply = function() {
            const input = document.getElementById('widgetInput');
            if (!input || !activeChatId) return;
            const text = input.value.trim();
            if (!text) return;
            input.value = '';

            fetch(`/admin/chats/${activeChatId}/reply`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ message: text })
            })
            .then(res => res.json())
            .then(data => {
                loadWidgetConversation();
            });
        };

        // Export local helpers to global window object
        window.openChatList = openChatList;
        window.closeWidgetConversation = closeWidgetConversation;
    })();
</script>
@endauth

