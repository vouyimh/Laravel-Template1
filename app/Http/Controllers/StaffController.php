<?php

namespace App\Http\Controllers;

use App\Models\Staff;

class StaffController extends Controller
{
    public function staffList()
    {
        $staff = Staff::orderByDesc('StaffID')->get();
        return view('admin.staff.pages-staff-list', compact('staff'));
        //return view('admin.staff.pages-staff-list');
    }

    public function addStaff()
    {
        return view('admin.staff.pages-staff-add');
    }
}
