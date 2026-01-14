<?php

namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;

class StaffController extends Controller
{
public function update(Request $request, $id)
{
    $staff = Staff::findOrFail($id);

    $request->validate([
        'FirstName' => 'required|string',
        'LastName' => 'required|string',
        'Email' => "required|email|unique:Staff,Email,{$id},StaffID",
        'Role' => 'required|string',
        'Username' => 'required|string',
    ]);

    $staff->update([
        'FirstName' => $request->FirstName,
        'LastName' => $request->LastName,
        'Email' => $request->Email,
        'Role' => $request->Role,
        'Username' => $request->Username,
        'PhoneNumber' => $request->PhoneNumber,
    ]);

    // Update password if provided
    if ($request->Password) {
        $staff->Password = bcrypt($request->Password);
        $staff->save();
    }

    // Handle profile picture
    if ($request->hasFile('ProfilePicture')) {
        $file = $request->file('ProfilePicture');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('uploads/staff'), $filename);
        $staff->ProfilePicture = $filename;
        $staff->save();
    }
    return view('admin.staff.add-staff');
}

}
