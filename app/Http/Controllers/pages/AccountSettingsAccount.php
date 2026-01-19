<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\User;

class AccountSettingsAccount extends Controller
{
    public function index()
    {
        return view('content.pages.pages-account-settings-account');
    }

    public function update(Request $request)
    {
        // ✅ TEST MODE (no login) - update the first admin
        $user = User::where('role', 'admin')->first();

        if (!$user) {
            abort(404, 'Admin user not found. Please create an admin in users table.');
        }

        $data = $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone'        => 'required|string|max:50',

            'organization' => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:255',
            'state'        => 'nullable|string|max:100',
            'zip_code'     => 'nullable|string|max:20',
            'country'      => 'nullable|string|max:100',
            'language'     => 'nullable|string|max:10',
            'timezone'     => 'nullable|string|max:50',
            'currency'     => 'nullable|string|max:10',

            'avatar'       => 'nullable|image|mimes:jpg,jpeg,png|max:800',

            // ✅ password fields (optional)
            'current_password' => 'nullable|string',
            'password'         => 'nullable|string|min:8|confirmed',
        ]);

        /**
         * ✅ Password: update only if user provided a new password
         */
        if (empty($data['password'] ?? null)) {
            // user didn't type new password → don't touch password in DB
            unset($data['password']);
        } else {
            // user wants change password → must verify current password
            if (empty($data['current_password'] ?? null) || !Hash::check($data['current_password'], $user->password)) {
                return back()
                    ->withErrors(['current_password' => 'Current password is incorrect'])
                    ->withInput();
            }
            // password will be hashed automatically (because User model has cast: 'password' => 'hashed')
        }

        /**
         * ✅ Avatar upload
         */
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        // remove non-db fields
        unset($data['avatar'], $data['current_password']);

        $user->update($data);

        return back()->with('success', 'Account updated successfully (TEST MODE)');
    }
}
