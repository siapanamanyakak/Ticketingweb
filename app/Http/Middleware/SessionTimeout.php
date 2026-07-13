<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionTimeout
{
    protected int $timeout = 60;

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return $next($request);
        }

        $lastActivity = session('last_activity');

        if ($lastActivity && (time() - $lastActivity) > ($this->timeout * 60)) {
            // Hapus session saja, tapi JANGAN hapus remember cookie
            session()->forget('last_activity');
            session()->flush();
            session()->regenerate();

            // Redirect ke login — kalau ada remember cookie
            // Laravel akan auto-login via remember token
            return redirect()->route('login')
                ->with('status', 'Sesi berakhir. Silakan login kembali.');
        }

        session(['last_activity' => time()]);

        return $next($request);
    }
}
