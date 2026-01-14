<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Staff;

class StaffListController extends Controller
{
    public function index()
    {
        $staff = Staff::orderByDesc('StaffID')->get();
        return view('admin.staff.pages-staff-list', compact('staff'));
    }
}
