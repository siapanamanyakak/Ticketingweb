<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
{
    // Belum login → mental ke login
    if (!auth()->check()) {
        return redirect()->route('login')
            ->with('error', 'Please log in first.');
    }

    // Sudah login tapi role salah → kembalikan ke homepage role sendiri
    if (!in_array(auth()->user()->role, $roles)) {
        $fallback = match(auth()->user()->role) {
            'it_supervisor' => route('supervisor.dashboard'),
            'it_support'    => route('support.tickets.index'),
            default         => route('user.tickets.index'),
        };

        return redirect($fallback)
            ->with('error', 'Access denied. You do not have permission to view that page.');
    }

    return $next($request);
}
}
