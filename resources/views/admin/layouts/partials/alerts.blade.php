@if (session('success') || session('error') || $errors->any())
    <div class="custom-alert-container">
        @if (session('success'))
            <div class="custom-alert success">
                <div class="icon">
                    <i class="bi bi-check-circle-fill"></i>
                </div>
                <div class="content">
                    <div class="title">Sukses</div>
                    <div class="message">{{ session('success') }}</div>
                </div>
                <button class="close-btn" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="custom-alert danger">
                <div class="icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="content">
                    <div class="title">Error</div>
                    <div class="message">{{ session('error') }}</div>
                </div>
                <button class="close-btn" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        @endif

        @if ($errors->any())
            <div class="custom-alert danger">
                <div class="icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <div class="content">
                    <div class="title">Validasi Gagal</div>
                    <div class="message">
                        <ul style="margin: 0; padding-left: 16px;">
                            @foreach ($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button class="close-btn" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        @endif
    </div>

    <style>
        .custom-alert-container {
            position: fixed;
            top: 24px;
            right: 24px;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 380px;
            width: 100%;
            pointer-events: none;
        }

        .custom-alert {
            pointer-events: auto;
            display: flex;
            align-items: flex-start;
            padding: 16px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #ccc;
            animation: slideIn 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            transition: all 0.3s ease;
        }

        [data-theme="dark"] .custom-alert {
            background: rgba(30, 41, 59, 0.95);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .custom-alert.success {
            border-left-color: #10B981;
        }

        .custom-alert.danger {
            border-left-color: #EF4444;
        }

        .custom-alert .icon {
            margin-right: 12px;
            font-size: 20px;
            flex-shrink: 0;
        }

        .custom-alert.success .icon {
            color: #10B981;
        }

        .custom-alert.danger .icon {
            color: #EF4444;
        }

        .custom-alert .content {
            flex-grow: 1;
            margin-right: 8px;
        }

        .custom-alert .content .title {
            font-weight: 700;
            font-size: 14px;
            color: #1E293B;
            margin-bottom: 2px;
        }

        [data-theme="dark"] .custom-alert .content .title {
            color: #F8FAFC;
        }

        .custom-alert .content .message {
            font-size: 13px;
            color: #64748B;
            line-height: 1.4;
        }

        [data-theme="dark"] .custom-alert .content .message {
            color: #94A3B8;
        }

        .custom-alert .close-btn {
            background: none;
            border: none;
            color: #94A3B8;
            cursor: pointer;
            font-size: 18px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.2s;
        }

        .custom-alert .close-btn:hover {
            color: #475569;
        }

        @keyframes slideIn {
            from {
                transform: translateX(120%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            to {
                transform: translateX(50px);
                opacity: 0;
            }
        }
    </style>

    <script>
        document.querySelectorAll('.custom-alert').forEach(alert => {
            setTimeout(() => {
                if (alert && alert.parentElement) {
                    alert.style.animation = 'fadeOut 0.5s ease forwards';
                    setTimeout(() => alert.remove(), 500);
                }
            }, 4000);
        });
    </script>
@endif
