<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request and verify user role slug.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Session::get('auth_user');

        if (!$user) {
            return redirect()->route('login')->with('warning', 'Sesi belum aktif. Silakan login.');
        }

        $userRole = $user['role_slug'] ?? null;

        if (!empty($roles) && !in_array($userRole, $roles, true)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses ditolak: Peran Anda tidak memiliki wewenang untuk tindakan ini.'
                ], 403);
            }

            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Anda tidak memiliki izin untuk mengakses modul tersebut.');
        }

        return $next($request);
    }
}
