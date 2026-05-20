<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Chatroom;
use App\Models\Emoji;

class RoomController extends Controller
{
    public function index()
    {
        $user     = Auth::user();
        $userRole = $user->role ?? 'client';

        $supportRoom = $this->getOrCreateSupportRoom($userRole);

        return redirect()->route('room', ['roomId' => $supportRoom->id]);
    }

    public function oneRoom(Request $request, $roomId)
    {
        $user = Auth::user();

        $data = [
            'user'          => $user,
            'rooms'         => $this->getVisibleRooms($user),
            'emojis'        => Emoji::all(),
            'appName'       => config('app.name'),
            'confettiWords' => env('APP_CONFETTI_WORDS'),
        ];

        return view('chat.index', [
            'data'   => $data,
            'roomId' => $roomId,
        ]);
    }

    private function getVisibleRooms($user)
    {
        if ($user->role === 'client') {
            return collect();
        }

        return Chatroom::whereNull('private_room_id')->get();
    }

    private function getOrCreateSupportRoom($role)
    {
        $roomName = ucfirst($role) . ' Support';

        return Chatroom::firstOrCreate(
            ['name' => $roomName],
            [
                'description'    => "Support room for {$role} users",
                'private_room_id' => null,
            ]
        );
    }
}
