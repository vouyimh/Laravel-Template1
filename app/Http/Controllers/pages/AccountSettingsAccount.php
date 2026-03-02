<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AccountSettingsAccount extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        return view('content.pages.pages-account-settings-account', [
            'user' => $user
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        if (!$user) return redirect()->route('login');

        // ✅ Build phone from parts if your form sends phone_country + phone_local
        if ($request->has('phone_country') || $request->has('phone_local')) {
            $request->merge([
                'phone' => ($request->phone_country ?? '') . preg_replace('/\D/', '', $request->phone_local ?? ''),
            ]);
        }

        $data = $request->validate([
            'first_name' => 'nullable|string|max:100',
            'last_name'  => 'nullable|string|max:100',
            'email'      => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],

            // ✅ allow update without phone
            'phone'      => ['nullable','string','max:50','regex:/^\+\d{7,15}$/'],

            'organization' => 'nullable|string|max:255',
            'address'      => 'nullable|string|max:255',
            'state'        => 'nullable|string|max:100',
            'zip_code'     => 'nullable|string|max:20',
            'country'      => 'nullable|string|max:100',
            'language'     => 'nullable|string|max:10',
            'timezone'     => 'nullable|string|max:50',
            'currency'     => 'nullable|string|max:10',

            'avatar' => 'nullable|image|mimes:jpg,jpeg,png|max:800',

            'current_password' => 'nullable|string',
            'password'         => 'nullable|string|min:8|confirmed',
        ]);

        // ✅ If password provided, require correct current password
        if (!empty($data['password'] ?? null)) {
            if (empty($data['current_password'] ?? null) || !Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect'])->withInput();
            }
        } else {
            unset($data['password']);
        }

        // ✅ Avatar upload
        if ($request->hasFile('avatar')) {
            if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        unset($data['avatar'], $data['current_password']);

        // ✅ Only update fields actually sent (avoid overwriting with nulls)
        $data = array_filter($data, fn($v) => $v !== null);

        $user->update($data);

        return back()->with('success', 'Account updated successfully');
    }
}
