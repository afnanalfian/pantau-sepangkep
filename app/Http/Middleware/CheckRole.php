<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Usage: ->middleware('role:admin,inda')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $role = session('role');

        if (!$role) {
            return redirect()->route('login')->with('error', 'Silakan login terlebih dahulu.');
        }

        // admin selalu punya akses penuh
        if ($role === 'admin') {
            return $next($request);
        }

        if (!in_array($role, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
