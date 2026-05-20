<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffController extends Controller
{
    public function index()
    {
        $staff = Staff::orderByDesc('StaffID')->get();
        return response()->json($staff);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'FirstName'      => ['required', 'string', 'max:50'],
            'LastName'       => ['required', 'string', 'max:50'],
            'Email'          => ['required', 'email', 'max:100', 'unique:Staff,Email'],
            'Role'           => ['required', 'in:Temporary,Permanent,Company'],
            'EmploymentType' => ['required', 'string', 'max:30'],
            'Username'       => ['required', 'string', 'max:50', 'unique:Staff,Username'],
            'Password'       => ['required', 'string', 'min:6'],
            'PhoneNumber'    => ['nullable', 'string', 'max:20'],
            'Address'        => ['nullable', 'string', 'max:255'],
            'ProfilePicture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $profilePath = null;
        if ($request->hasFile('ProfilePicture')) {
            $profilePath = $request->file('ProfilePicture')->store('staff', 'public');
        }

        $staff = Staff::create([
            'FirstName'      => trim($validated['FirstName']),
            'LastName'       => trim($validated['LastName']),
            'Email'          => trim($validated['Email']),
            'Role'           => $validated['Role'],
            'EmploymentType' => $validated['EmploymentType'],
            'Username'       => trim($validated['Username']),
            'Password'       => bcrypt($validated['Password']),
            'PhoneNumber'    => $validated['PhoneNumber'] ?? null,
            'Address'        => $validated['Address'] ?? null,
            'ProfilePicture' => $profilePath,
        ]);

        return response()->json(['success' => true, 'staff' => $staff], 201);
    }

    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);

        if (!empty($staff->ProfilePicture)) {
            Storage::disk('public')->delete($staff->ProfilePicture);
        }

        $staff->delete();

        return response()->json(['success' => true, 'message' => 'Staff deleted successfully.']);
    }
}
