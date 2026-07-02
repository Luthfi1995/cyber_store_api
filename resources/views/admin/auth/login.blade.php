<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login Admin — ubsiStore</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{asset('/assets/css/login.css')}}?v=6">
</head>
<body>
<div class="login-wrap">
    <!-- Left Column: Logo & Brand Info -->
    <div class="login-left">
        @php
            $loginLogoSetting = \App\Models\Setting::get('store_logo');
            $loginLogoUrl = $loginLogoSetting ? Storage::disk('public')->url($loginLogoSetting) : asset('assets/img/logo.png');
        @endphp
        <img src="{{ $loginLogoUrl }}" alt="UBSI Store Logo" class="brand-logo">
        <h1 class="brand-title">{{ \App\Models\Setting::get('store_name', 'UBSI Store') }}</h1>
        <p class="brand-subtitle">Panel Administrasi Toko</p>
    </div>

    <!-- Right Column: Form & Footer -->
    <div class="login-right">
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
            © {{ date('Y') }} UBSI Store. Admin Panel v1.0
        </div>
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
