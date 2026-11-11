<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Roles can be separated by pipe or comma: 'role:ADMIN|MARKETING'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        if (!$user->is_active) {
            Auth::logout();
            return redirect()->route('login')->withErrors(['email' => 'Akun Anda telah dinonaktifkan oleh Administrator.']);
        }

        // Support role:ADMIN,MARKETING or role:ADMIN|MARKETING
        $allowedRoles = [];
        foreach ($roles as $r) {
            foreach (explode('|', $r) as $part) {
                $allowedRoles[] = trim(strtoupper($part));
            }
        }

        $userRole = strtoupper($user->role->code ?? '');

        // ADMIN has superuser permission
        if ($userRole === 'ADMIN') {
            return $next($request);
        }

        if (!in_array($userRole, $allowedRoles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak: Divisi Anda tidak memiliki otorisasi untuk tindakan ini.'], 403);
            }
            return redirect()->route('dashboard')->with('error', 'Akses ditolak: Divisi Anda tidak memiliki otorisasi untuk mengakses fitur tersebut.');
        }

        return $next($request);
    }
}
