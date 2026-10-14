<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Session::has('auth_user') || !Session::has('jwt_token')) {
            // For convenience, if accessing dashboard or any module without login, redirect to login
            return redirect()->route('login')->with('warning', 'Sesi Anda belum aktif atau telah berakhir. Silakan login.');
        }

        return $next($request);
    }
}
