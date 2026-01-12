<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;

class StaffDelete extends Controller
{
    // Show staff list for deletion (optional)
    public function index()
    {
        $staff = Staff::orderByDesc('StaffID')->get();
        return view('content.pages.pages-staff-delete', compact('staff'));
    }

    // Delete a staff
    public function destroy(Request $request, $id)
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()->route('pages-staff-delete')->with('success', 'Staff deleted successfully!');
    }
}
