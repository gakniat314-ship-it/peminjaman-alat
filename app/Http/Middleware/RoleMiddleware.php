<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Membatasi akses route berdasarkan role user.
 *
 * Contoh pemakaian di route:
 *   ->middleware('role:admin')            hanya admin
 *   ->middleware('role:admin,petugas')    admin ATAU petugas
 */
class RoleMiddleware
{
    /** Memastikan pengguna sudah login dan memiliki salah satu role yang diizinkan. */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Belum login -> arahkan ke halaman login
        if (! $user) {
            return redirect()->route('login');
        }

        // Sudah login tapi role tidak diizinkan -> 403 Forbidden
        if (! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki hak akses ke halaman ini.');
        }

        return $next($request);
    }
}