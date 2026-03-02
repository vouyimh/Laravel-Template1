<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login'); // Make sure this view exists
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        // Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempt login
        if (Auth::attempt($request->only('email', 'password'))) {
            $user = Auth::user();

            // Call authenticated method
            return $this->authenticated($request, $user);
        }

        // Login failed
        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->withInput($request->only('email'));
    }

    /**
     * After login, handle 2FA redirection
     */
    protected function authenticated(Request $request, $user)
    {
        // Temporarily store user ID in session for 2FA verification
        session(['2fa:user:id' => $user->id]);

        // Logout user until 2FA verification
        auth()->logout();

        // Redirect to 2FA verify page
        return redirect('/2fa-verify');
    }

    /**
     * Logout the user
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
