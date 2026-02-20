<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PragmaRX\Google2FA\Google2FA;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TwoFactorController extends Controller
{
    public function setup(Request $request)
    {
        $user = auth()->user();
        $google2fa = new Google2FA();

        // ✅ If user already enabled 2FA
        if (!empty($user->two_factor_secret)) {

            // Use existing secret from database
            $secret = $user->two_factor_secret;

            // Store it in session temporarily for verification
            $request->session()->put('2fa_secret', $secret);

            // Do NOT generate QR
            return view('auth.2fa-verify', [
                'qrCode' => null,   // No QR
            ]);
        }

        // ✅ First time setup → generate new secret
        $secret = $google2fa->generateSecretKey();
        $request->session()->put('2fa_secret', $secret);

        $otpAuthUrl = $google2fa->getQRCodeUrl(
            'Bionett Tours',
            $user->email,
            $secret
        );

        $qrCode = QrCode::size(200)->generate($otpAuthUrl);

        return view('auth.2fa-verify', [
            'qrCode' => $qrCode,
        ]);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'token' => 'required|digits:6'
        ]);

        $google2fa = new Google2FA();
        $secret = $request->session()->get('2fa_secret');

         $window = 1000; 

        $valid = $google2fa->verifyKey($secret, $request->token, $window);

        if ($valid) {
            $user = auth()->user();

            // Store the 2FA secret in the database if not already set
            if (empty($user->two_factor_secret)) {
                $user->two_factor_secret = $secret;
                $user->save();
            }
            
            $request->session()->put('2fa_verified', true);
            return redirect()->intended($this->redirectByRole($user));
        }

        return back()->withErrors(['Invalid verification code']);
    }

    private function redirectByRole($user)
    {
        if ($user->role === 'admin') {
            return route('admin.dashboard');
        }

        if ($user->role === 'staff') {
            return route('staff.staff.dashboard');
        }

        if ($user->role === 'client') {
            return route('client.dashboard');
        }

        return '/';
    }
}
