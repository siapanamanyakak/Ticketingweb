<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        if (!$request->expectsJson()) {
            // Simpan flash message sebelum redirect
            session()->flash('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
            return route('login');
        }

        return null;
    }
}
