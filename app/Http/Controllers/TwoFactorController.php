<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class TwoFactorController extends Controller
{
    /**
     * Show the 2FA setup / verify page.
     * If the user already has a secret, show the verify form (no QR).
     * If first time, generate a secret and show the QR to scan.
     */
    public function setup(Request $request)
    {
        $user      = auth()->user();
        $google2fa = new Google2FA();

        if (!empty($user->two_factor_secret)) {
            // Already set up — just ask for a token
            $request->session()->put('2fa_secret', $user->two_factor_secret);

            return view('auth.2fa-verify', ['qrCode' => null]);
        }

        // First-time setup — generate a new secret
        $secret = $google2fa->generateSecretKey();
        $request->session()->put('2fa_secret', $secret);

        $otpAuthUrl = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $secret
        );

        $qrCode = QrCode::size(200)->generate($otpAuthUrl);

        return view('auth.2fa-verify', ['qrCode' => $qrCode]);
    }

    /**
     * Verify the submitted 2FA token.
     * Window of 1 allows one 30-second slot of drift on either side.
     */
    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required|digits:6',
        ]);

        $google2fa = new Google2FA();
        $secret    = $request->session()->get('2fa_secret');

        $valid = $google2fa->verifyKey($secret, $request->token, 1);

        if (!$valid) {
            return back()->withErrors(['token' => 'Invalid or expired verification code. Please try again.']);
        }

        $user = auth()->user();

        if (empty($user->two_factor_secret)) {
            $user->two_factor_secret = $secret;
            $user->save();
        }

        $request->session()->put('2fa_verified', true);

        return redirect($this->redirectByRole($user));
    }

    private function redirectByRole($user): string
    {
        return match ($user->role) {
            'admin'  => route('dashboard-analytics'),
            'staff'  => route('staff.task'),
            'client' => route('client.task'),
            default  => '/',
        };
    }
}
