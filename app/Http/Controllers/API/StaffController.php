<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    // ✅ LIST PAGE
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

    // ✅ SAVE NEW STAFF (POST)
    public function storeStaff(Request $request)
    {
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

        $validated['FirstName'] = trim($validated['FirstName']);
        $validated['LastName']  = trim($validated['LastName']);
        $validated['Email']     = trim($validated['Email']);
        $validated['Username']  = trim($validated['Username']);
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

            return redirect()
                ->route('admin.staff.pages-staff-list')
                ->with('success', 'Staff added successfully');

        } catch (\Throwable $e) {
            DB::rollBack();

            if ($profilePath) {
                Storage::disk('public')->delete($profilePath);
            }

            throw $e;
        }
    }

    // ✅ SHOW EDIT FORM
    public function editStaff($id)
    {
        $staff = Staff::findOrFail($id);
        return view('admin.staff.pages-staff-edit', compact('staff'));
    }

    // ✅ UPDATE STAFF (PUT)
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

        // Remove photo
        if ($request->input('RemoveProfilePicture') == '1') {
            if ($staff->ProfilePicture) {
                Storage::disk('public')->delete($staff->ProfilePicture);
            }
            $staff->ProfilePicture = null;
        }

        // Upload new photo
        if ($request->hasFile('ProfilePicture')) {
            if ($staff->ProfilePicture) {
                Storage::disk('public')->delete($staff->ProfilePicture);
            }
            $staff->ProfilePicture = $request->file('ProfilePicture')->store('staff', 'public');
        }

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

        return redirect()
            ->route('admin.staff.pages-staff-list')
            ->with('success', 'Staff updated successfully!');
    }

    // ✅ DELETE STAFF
    public function deleteStaff(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        if (!empty($staff->ProfilePicture)) {
            Storage::disk('public')->delete($staff->ProfilePicture);
        }

        $staff->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('admin.staff.pages-staff-list')
            ->with('success', 'Staff deleted successfully!');
    }
}
