<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class StaffController extends Controller
{
    // ✅ LIST STAFF
    public function staffList()
    {
        $staff = Staff::orderByDesc('StaffID')->get();
        return view('admin.staff.pages-staff-list', compact('staff'));
    }

    // ✅ SHOW ADD FORM
    public function addStaff()
    {
        return view('admin.staff.pages-staff-add');
    }

    // ✅ STORE NEW STAFF
    public function storeStaff(Request $request)
    {
        $validated = $request->validate([
            'FirstName' => ['required','string','max:50'],
            'LastName'  => ['required','string','max:50'],
            'Email'     => ['required','email','max:100','unique:Staff,Email'],
            'Role'      => ['required','in:Temporary,Permanent,Company'],
            'EmploymentType' => ['required','string','max:30'],
            'Username'  => ['required','string','max:50','unique:Staff,Username'],
            'Password'  => ['required','string','min:6'],
            'PhoneNumber' => ['nullable','string','max:20'],
            'ProfilePicture' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        DB::beginTransaction();
        try {
            $profilePath = null;
            if ($request->hasFile('ProfilePicture')) {
                $profilePath = $request->file('ProfilePicture')->store('staff', 'public');
            }

            Staff::create([
                'FirstName' => trim($validated['FirstName']),
                'LastName'  => trim($validated['LastName']),
                'Email'     => trim($validated['Email']),
                'Role'      => $validated['Role'],
                'EmploymentType' => $validated['EmploymentType'],
                'Username'  => trim($validated['Username']),
                'Password'  => bcrypt($validated['Password']),
                'PhoneNumber' => isset($validated['PhoneNumber']) ? trim($validated['PhoneNumber']) : null,
                'ProfilePicture' => $profilePath,
            ]);

            DB::commit();
            return redirect()->route('admin.staff.pages-staff-list')->with('success', 'Staff added successfully');
        } catch (\Throwable $e) {
            DB::rollBack();
            if (isset($profilePath)) Storage::disk('public')->delete($profilePath);
            throw $e;
        }
    }

    // ✅ SHOW EDIT FORM
    public function editStaff($id)
    {
        $staff = Staff::findOrFail($id);
        return view('admin.staff.pages-staff-edit', compact('staff'));
    }

    // ✅ UPDATE STAFF
    public function updateStaff(Request $request, $id)
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

        // Remove profile picture
        if ($request->input('RemoveProfilePicture') == '1' && $staff->ProfilePicture) {
            Storage::disk('public')->delete($staff->ProfilePicture);
            $staff->ProfilePicture = null;
        }

        // Upload new profile picture
        if ($request->hasFile('ProfilePicture')) {
            if ($staff->ProfilePicture) {
                Storage::disk('public')->delete($staff->ProfilePicture);
            }
            $staff->ProfilePicture = $request->file('ProfilePicture')->store('staff', 'public');
        }

        // Update fields
        $staff->FirstName = trim($validated['FirstName']);
        $staff->LastName  = trim($validated['LastName']);
        $staff->Email     = trim($validated['Email']);
        $staff->Role      = $validated['Role'];
        $staff->Username  = trim($validated['Username']);
        $staff->PhoneNumber = isset($validated['PhoneNumber']) ? trim($validated['PhoneNumber']) : null;

        if (!empty($validated['Password'])) {
            $staff->Password = bcrypt($validated['Password']);
        }

        $staff->save();

        return redirect()->route('admin.staff.pages-staff-list')->with('success', 'Staff updated successfully!');
    }

    // ✅ DELETE STAFF
    public function deleteStaff($id)
    {
        $staff = Staff::findOrFail($id);

        if ($staff->ProfilePicture) {
            Storage::disk('public')->delete($staff->ProfilePicture);
        }

        $staff->delete();

        return redirect()->route('admin.staff.pages-staff-list')->with('success', 'Staff deleted successfully!');
    }
}

