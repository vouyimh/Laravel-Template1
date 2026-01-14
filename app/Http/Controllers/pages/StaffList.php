<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Staff;

class StaffList extends Controller
{
    public function index()
    {
        $staff = Staff::orderByDesc('StaffID')->get();

        return view('content.pages.pages-staff-list', compact('staff'));
    }
}
