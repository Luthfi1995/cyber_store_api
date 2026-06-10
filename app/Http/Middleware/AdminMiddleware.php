<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::guard('web')->check()) {
            return redirect()->route('admin.login')->with('error', 'Silakan login terlebih dahulu.');
        }

        $user = Auth::guard('web')->user();

        if (!$user->is_active) {
            Auth::guard('web')->logout();
            return redirect()->route('admin.login')->with('error', 'Akun Anda tidak aktif.');
        }

        if (!in_array($user->role, ['superadmin', 'admin'])) {
            Auth::guard('web')->logout();
            return redirect()->route('admin.login')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
