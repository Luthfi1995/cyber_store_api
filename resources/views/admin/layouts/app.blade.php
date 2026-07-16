<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="ubsiStore Admin Panel — Kelola produk dan pengguna toko Anda.">
    <title>@yield('title', 'Dashboard') — {{ \App\Models\Setting::get('store_name', 'BSI Cyber Store') }} Admin</title>
    @php
    $faviconSetting = \App\Models\Setting::get('store_logo');
    $faviconUrl = $faviconSetting ? Storage::disk('public')->url($faviconSetting) : asset('assets/img/logo-cyberstore.jpg');
    @endphp
    <link rel="icon" href="{{ $faviconUrl }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}?v=2">
    <script>
        (function() {
            const currentTheme = localStorage.getItem('theme') || 'dark';
            document.documentElement.setAttribute('data-theme', currentTheme);
        })();
    </script>
    @stack('styles')
</head>

<body>

    <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

    @include('admin.layouts.partials.sidebar')

    <!-- ─── MAIN ──────────────────────────────────────────────────────────── -->
    <div class="main-content">
        @include('admin.layouts.partials.topbar')

        <main class="page-content">
            @include('admin.layouts.partials.alerts')

            @yield('content')
        </main>
    </div>

    @include('admin.layouts.partials.modals')

    @include('admin.layouts.partials.scripts')
    @stack('scripts')
</body>

</html>