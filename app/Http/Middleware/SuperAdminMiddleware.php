<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::guard('web')->user();

        if (!$user || $user->role !== 'superadmin') {
            return redirect()->back()->with('error', 'Aksi ini hanya dapat dilakukan oleh Superadmin.');
        }

        return $next($request);
    }
}
