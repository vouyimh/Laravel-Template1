<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /** Return the 20 most recent notifications (read + unread) */
    public function index()
    {
        $notifications = Auth::user()
            ->notifications()
            ->take(20)
            ->get()
            ->map(fn($n) => [
                'id'         => $n->id,
                'type'       => $n->type,
                'data'       => $n->data,
                'read_at'    => $n->read_at,
                'created_at' => $n->created_at->diffForHumans(),
            ]);

        return response()->json($notifications);
    }

    /** Return only the unread notification count */
    public function unreadCount()
    {
        return response()->json([
            'count' => Auth::user()->unreadNotifications()->count(),
        ]);
    }

    /** Mark a single notification as read */
    public function markAsRead(string $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /** Mark all unread notifications as read */
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }
}
