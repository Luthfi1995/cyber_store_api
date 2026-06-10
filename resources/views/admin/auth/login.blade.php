<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Admin — ubsiStore</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --bg-dark:    #0a0f1e;
            --bg-card:    #111827;
            --bg-input:   #1a2235;
            --border:     rgba(255,255,255,0.08);
            --accent:     #4f6ef7;
            --accent-hover:#3b5cf6;
            --text-primary:#f1f5f9;
            --text-secondary:#94a3b8;
            --text-muted: #64748b;
            --danger:     #ef4444;
            --danger-light:rgba(239,68,68,0.12);
            --radius:     14px;
            --radius-sm:  8px;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        /* Animated background blobs */
        body::before, body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.15;
            pointer-events: none;
        }
        body::before {
            width: 500px; height: 500px;
            background: radial-gradient(circle, #4f6ef7, transparent);
            top: -100px; left: -100px;
            animation: blob1 8s ease-in-out infinite alternate;
        }
        body::after {
            width: 400px; height: 400px;
            background: radial-gradient(circle, #7c3aed, transparent);
            bottom: -100px; right: -50px;
            animation: blob2 10s ease-in-out infinite alternate;
        }
        @keyframes blob1 { from { transform: translate(0,0) scale(1); } to { transform: translate(40px,30px) scale(1.1); } }
        @keyframes blob2 { from { transform: translate(0,0) scale(1); } to { transform: translate(-30px,20px) scale(1.15); } }

        .login-wrap {
            width: 100%;
            max-width: 420px;
            position: relative;
            z-index: 10;
        }

        .login-brand {
            text-align: center;
            margin-bottom: 32px;
        }
        .login-brand-icon {
            width: 64px; height: 64px;
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
            font-size: 28px;
            margin: 0 auto 16px;
            box-shadow: 0 8px 32px rgba(79,110,247,0.35);
        }
        .login-brand h1 {
            font-size: 26px; font-weight: 800;
            color: var(--text-primary);
            letter-spacing: -0.5px;
        }
        .login-brand p {
            font-size: 14px; color: var(--text-muted);
            margin-top: 4px;
        }

        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 32px;
            box-shadow: 0 24px 64px rgba(0,0,0,0.5);
            animation: fadeIn 0.4s ease;
        }
        @keyframes fadeIn { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }

        .login-title {
            font-size: 18px; font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 24px;
        }

        .alert-danger {
            background: var(--danger-light);
            border: 1px solid rgba(239,68,68,0.3);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: 13px;
            color: var(--danger);
            margin-bottom: 20px;
        }
        .alert-success {
            background: rgba(16,185,129,0.12);
            border: 1px solid rgba(16,185,129,0.3);
            border-radius: var(--radius-sm);
            padding: 10px 14px;
            font-size: 13px;
            color: #10b981;
            margin-bottom: 20px;
        }

        .form-group { margin-bottom: 18px; }
        .form-label {
            display: block;
            font-size: 13px; font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 6px;
        }
        .form-control {
            width: 100%;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: var(--radius-sm);
            padding: 11px 14px;
            font-size: 14px;
            color: var(--text-primary);
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(79,110,247,0.15);
        }
        .form-control::placeholder { color: var(--text-muted); }
        .field-error { font-size: 12px; color: var(--danger); margin-top: 4px; }

        .input-icon-wrap { position: relative; }
        .input-icon {
            position: absolute;
            left: 12px; top: 50%;
            transform: translateY(-50%);
            font-size: 16px; color: var(--text-muted);
            pointer-events: none;
        }
        .input-icon-wrap .form-control { padding-left: 38px; }

        .remember-row {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 22px;
        }
        .remember-row input { accent-color: var(--accent); width: 16px; height: 16px; cursor: pointer; }
        .remember-row label { font-size: 13px; color: var(--text-secondary); cursor: pointer; }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, var(--accent), #7c3aed);
            color: white;
            border: none;
            border-radius: var(--radius-sm);
            padding: 12px;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 16px rgba(79,110,247,0.3);
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(79,110,247,0.45);
        }
        .btn-login:active { transform: translateY(0); }

        .login-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-muted);
        }

        .demo-box {
            background: rgba(79,110,247,0.08);
            border: 1px solid rgba(79,110,247,0.2);
            border-radius: var(--radius-sm);
            padding: 14px 16px;
            margin-top: 20px;
        }
        .demo-title {
            font-size: 11px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.8px;
            color: var(--accent); margin-bottom: 10px;
        }
        .demo-item {
            display: flex; align-items: center; gap: 8px;
            font-size: 12px; color: var(--text-secondary);
            margin-bottom: 5px;
        }
        .demo-badge {
            display: inline-flex;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 10px; font-weight: 600;
        }
        .demo-sa { background:rgba(239,68,68,0.15); color:#ef4444; }
        .demo-ad { background:rgba(245,158,11,0.15); color:#f59e0b; }
        #togglePassword svg {
            width: 18px;
            height: 18px;
            stroke: var(--text-muted);
            transition: stroke 0.2s ease;
        }
        #togglePassword:hover svg {
            stroke: var(--text-primary);
        }
    </style>
</head>
<body>
<div class="login-wrap">
    <div class="login-brand">
        <div class="login-brand-icon">🛍️</div>
        <h1>ubsiStore</h1>
        <p>Panel Administrasi</p>
    </div>

    <div class="login-card">
        <div class="login-title">Masuk ke Admin Panel</div>

        @if($errors->any())
            <div class="alert-danger">{{ $errors->first() }}</div>
        @endif

        @if(session('error'))
            <div class="alert-danger">{{ session('error') }}</div>
        @endif

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('admin.login.submit') }}" id="loginForm">
            @csrf
            <div class="form-group">
                <label class="form-label" for="email">Email Admin</label>
                <div class="input-icon-wrap">
                    <span class="input-icon">✉️</span>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control"
                        value="{{ old('email') }}"
                        placeholder="admin@ubsistore.test"
                        required
                        autocomplete="email"
                    >
                </div>
                @error('email')
                    <div class="field-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-icon-wrap" style="position: relative;">
                    <span class="input-icon">🔒</span>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        style="padding-right: 42px;"
                    >
                    <button 
                        type="button" 
                        id="togglePassword" 
                        style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-muted); font-size: 16px; display: flex; align-items: center; justify-content: center; z-index: 10; padding: 4px;"
                        title="Tampilkan/Sembunyikan Password"
                    >
                        <i data-lucide="eye-off"></i>
                    </button>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya</label>
            </div>

            <button type="submit" class="btn-login" id="submitBtn">
                Masuk ke Panel
            </button>
        </form>

        <div class="demo-box">
            <div class="demo-title">🔑 Akun Demo</div>
            <div class="demo-item">
                <span class="demo-badge demo-sa">Superadmin</span>
                superadmin@ubsistore.test / password123
            </div>
            <div class="demo-item">
                <span class="demo-badge demo-ad">Admin</span>
                admin@ubsistore.test / password123
            </div>
        </div>
    </div>

    <div class="login-footer">
        © {{ date('Y') }} ubsiStore. Admin Panel v1.0
    </div>
</div>

<script src="https://unpkg.com/lucide@latest"></script>
<script>
// Initialize Lucide icons
lucide.createIcons();

document.getElementById('loginForm').addEventListener('submit', function() {
    const btn = document.getElementById('submitBtn');
    btn.textContent = 'Memproses...';
    btn.disabled = true;
});

document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const isPassword = passwordInput.getAttribute('type') === 'password';
    passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
    
    // Toggle the Lucide icon dynamically
    this.innerHTML = isPassword 
        ? '<i data-lucide="eye"></i>' 
        : '<i data-lucide="eye-off"></i>';
        
    // Re-initialize Lucide for the newly injected icon element
    lucide.createIcons();
});
</script>
</body>
</html>
