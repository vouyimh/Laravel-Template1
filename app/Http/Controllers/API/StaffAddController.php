<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StaffAddController extends Controller
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

            $validated['FirstName']   = trim($validated['FirstName']);
            $validated['LastName']    = trim($validated['LastName']);
            $validated['Email']       = trim($validated['Email']);
            $validated['Username']    = trim($validated['Username']);
            $validated['PhoneNumber'] = isset($validated['PhoneNumber']) ? trim($validated['PhoneNumber']) : null;

            $profilePath = null;

            DB::beginTransaction();
            try {
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

                // ✅ go back to list (/admin/staff-list)
                return redirect()
                    ->route('admin.staff.list')
                    ->with('success', 'Staff added successfully');

            } catch (\Throwable $e) {
                DB::rollBack();

                if ($profilePath) {
                    Storage::disk('public')->delete($profilePath);
                }

                throw $e;
            }
        }

        // ✅ view file: resources/views/admin/staff/pages-staff-add.blade.php
        return view('admin.staff.pages-staff-add');
    }
}
