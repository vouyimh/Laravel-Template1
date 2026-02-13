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
        $google2fa = new Google2FA();

        // Generate secret if not exists
        if (!$request->session()->has('2fa_secret')) {
            $secret = $google2fa->generateSecretKey();
            $request->session()->put('2fa_secret', $secret);
        } else {
            $secret = $request->session()->get('2fa_secret');
        }

        // IMPORTANT: pass (issuer, email, secret)
        $otpAuthUrl = $google2fa->getQRCodeUrl(
            'Bionett Tours',                 // Issuer / App Name
            auth()->user()->email,           // Account (VERY IMPORTANT)
            $secret                          // Secret
        );

        // Generate QR code
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
            return redirect('/dashboard');
        }

        return back()->withErrors(['Invalid verification code']);
    }
}
