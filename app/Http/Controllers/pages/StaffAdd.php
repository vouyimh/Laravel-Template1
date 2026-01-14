<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StaffAdd extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {

            $validated = $request->validate([
                'FirstName'      => ['required', 'string', 'max:50'],
                'LastName'       => ['required', 'string', 'max:50'],
                'Email'          => ['required', 'email', 'max:100', 'unique:Staff,Email'],
                'Role'           => ['required', 'in:Temporary,Permanent,Company'],
                'Username'       => ['required', 'string', 'max:50', 'unique:Staff,Username'],
                'Password'       => ['required', 'string', 'min:6'],
                'PhoneNumber'    => ['nullable', 'string', 'max:20'],
                'ProfilePicture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ]);

            // Clean values
            $validated['FirstName'] = trim($validated['FirstName']);
            $validated['LastName']  = trim($validated['LastName']);
            $validated['Email']     = trim($validated['Email']);
            $validated['Username']  = trim($validated['Username']);
            $validated['PhoneNumber'] = isset($validated['PhoneNumber']) ? trim($validated['PhoneNumber']) : null;

            $profilePath = null;

            DB::beginTransaction();
            try {
                // Upload file
                if ($request->hasFile('ProfilePicture')) {
                    $profilePath = $request->file('ProfilePicture')->store('staff', 'public');
                }

                Staff::create([
                    'FirstName'      => $validated['FirstName'],
                    'LastName'       => $validated['LastName'],
                    'Email'          => $validated['Email'],
                    'Role'           => $validated['Role'],
                    'Username'       => $validated['Username'],
                    'Password'       => bcrypt($validated['Password']),
                    'PhoneNumber'    => $validated['PhoneNumber'],
                    'ProfilePicture' => $profilePath,
                ]);

                DB::commit();

                return redirect()
                    ->route('pages-staff-list')
                    ->with('success', 'Staff added successfully');

            } catch (\Throwable $e) {
                DB::rollBack();

                // If file uploaded but DB failed, remove the file
                if ($profilePath) {
                    Storage::disk('public')->delete($profilePath);
                }

                throw $e; // or return back()->withErrors(...)
            }
        }

        return view('content.pages.pages-staff-add');
    }
}
