<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffEdit extends Controller
{
    public function index($id)
    {
        $staff = Staff::findOrFail($id);
        return view('content.pages.pages-staff-edit', compact('staff'));
    }

    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'FirstName' => ['required','string','max:50'],
            'LastName'  => ['required','string','max:50'],
            'Email'     => ["required","email","max:100","unique:Staff,Email,$id,StaffID"],
            'Role'      => ['required','in:Temporary,Permanent,Company'],
            'Username'  => ["required","string","max:50","unique:Staff,Username,$id,StaffID"],
            'PhoneNumber' => ['nullable','string','max:20'],
            'Password'  => ['nullable','string','min:6'],
            'ProfilePicture' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'RemoveProfilePicture' => ['nullable','in:0,1'],
        ]);

        // ✅ Remove profile picture (if user clicked remove)
        if ($request->input('RemoveProfilePicture') == '1') {
            if ($staff->ProfilePicture) {
                Storage::disk('public')->delete($staff->ProfilePicture);
            }
            $staff->ProfilePicture = null;
        }

        // ✅ Upload new profile picture (replace old)
        if ($request->hasFile('ProfilePicture')) {
            if ($staff->ProfilePicture) {
                Storage::disk('public')->delete($staff->ProfilePicture);
            }
            $staff->ProfilePicture = $request->file('ProfilePicture')->store('staff', 'public');
        }

        // ✅ Update fields
        $staff->FirstName = $validated['FirstName'];
        $staff->LastName  = $validated['LastName'];
        $staff->Email     = $validated['Email'];
        $staff->Role      = $validated['Role'];
        $staff->Username  = $validated['Username'];
        $staff->PhoneNumber = $validated['PhoneNumber'] ?? null;

        // ✅ Update password if provided
        if (!empty($validated['Password'])) {
            $staff->Password = bcrypt($validated['Password']);
        }

        $staff->save();

        return redirect()->route('pages-staff-list')->with('success', 'Staff updated successfully!');
    }
}
