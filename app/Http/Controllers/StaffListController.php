<?php

namespace App\Http\Controllers;

use App\Models\Staff;

class StaffListController extends Controller
{
    // GET /admin/staff-list
    public function staffList()
    {
        // $staff = Staff::orderByDesc('StaffID')->get(); // ✅ provide $staff to blade
        // return view('admin.staff.pages-staff-list', compact('staff'));
        return view('admin.staff.pages-staff-list');
    }

    // GET /admin/staff-add
    public function addStaff()
    {
        // ✅ your real file is: resources/views/admin/staff/pages-staff-add.blade.php
        return view('admin.staff.pages-staff-add');
    }
}
