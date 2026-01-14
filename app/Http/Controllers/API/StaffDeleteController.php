<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StaffDeleteController extends Controller
{
    // Optional: show staff list page for deletion
    public function index()
    {
        $staff = Staff::orderByDesc('StaffID')->get();
        return view('content.pages.pages-staff-delete', compact('staff'));
    }

    // Delete a staff
    public function destroy(Request $request, $id)
    {
        $staff = Staff::find($id);

        if (!$staff) {
            // For fetch/ajax requests
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Staff not found'
                ], 404);
            }

            return redirect()
                ->back()
                ->with('error', 'Staff not found');
        }

        // ✅ delete profile picture file (if exists)
        if (!empty($staff->ProfilePicture)) {
            Storage::disk('public')->delete($staff->ProfilePicture);
        }

        $staff->delete();

        // ✅ for SweetAlert fetch()
        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }

        // normal redirect fallback
        return redirect()
            ->route('pages-staff-list')
            ->with('success', 'Staff deleted successfully!');
    }
}
