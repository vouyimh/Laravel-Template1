<?php

namespace App\Http\Controllers\dashboard;

use App\Http\Controllers\Controller;

class TotalBooking extends Controller
{
    public function index()
    {
        return view('content.dashboard.total-booking');
    }
}
