<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactorIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        // If user is not logged in, let auth middleware handle
        if (!auth()->check()) {
            return $next($request);
        }

        // If 2FA is not verified in session, redirect to 2FA setup page
        if (!session('2fa_verified')) {
            return redirect('/2fa-setup');
        }

        return $next($request);
    }
}
