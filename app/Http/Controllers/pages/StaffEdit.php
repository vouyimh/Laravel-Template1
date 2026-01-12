<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffEdit extends Controller
{
    // Show the edit form
    public function index($id)
    {
        $staff = Staff::findOrFail($id);
        return view('content.pages.pages-staff-edit', compact('staff'));
    }

    // Handle update form submission
    public function update(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);

        $request->validate([
            'FirstName' => 'required|string',
            'LastName' => 'required|string',
            'Email' => "required|email|unique:Staff,Email,$id,StaffID",
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

        if($request->Password){
            $staff->Password = bcrypt($request->Password);
            $staff->save();
        }

        return redirect()->route('pages-staff-list')->with('success', 'Staff updated successfully!');
    }

    // Delete staff
    public function destroy($id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();
        return redirect()->route('pages-staff-list')->with('success', 'Staff deleted successfully!');
    }
}
