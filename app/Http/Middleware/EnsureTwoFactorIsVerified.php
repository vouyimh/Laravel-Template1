<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureTwoFactorIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return $next($request);
        }

        // Auto-verify if user hasn't configured 2FA yet, or has opted to skip it
        if (!session('2fa_verified')) {
            $user = auth()->user();
            if ($user->skip_2fa || empty($user->two_factor_secret)) {
                session(['2fa_verified' => true]);
            } else {
                if ($request->expectsJson()) {
                    return response()->json(['error' => '2FA verification required'], 403);
                }
                return redirect('/2fa-setup');
            }
        }

        return $next($request);
    }
}
