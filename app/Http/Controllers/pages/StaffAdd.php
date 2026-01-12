<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Staff;

class StaffAdd extends Controller
{
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {

            $validated = $request->validate([
                'FirstName'   => ['required','string','max:50'],
                'LastName'    => ['required','string','max:50'],
                'Email'       => ['required','email','max:100','unique:Staff,Email'],
                'Role'        => ['required','in:Temporary,Permanent,Company'],
                'Username'    => ['required','string','max:50','unique:Staff,Username'],
                'Password'    => ['required','string','min:6'],
                'PhoneNumber' => ['nullable','string','max:20'],
            ]);

            Staff::create([
                'FirstName'   => $validated['FirstName'],
                'LastName'    => $validated['LastName'],
                'Email'       => $validated['Email'],
                'Role'        => $validated['Role'],
                'Username'    => $validated['Username'],
                'Password'    => bcrypt($validated['Password']),
                'PhoneNumber' => $validated['PhoneNumber'] ?? null,
            ]);

            return redirect()
                ->route('pages-staff-list')
                ->with('success', 'Staff added successfully');
        }

        return view('content.pages.pages-staff-add');
    }
}
