<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Chatroom;
use App\Models\Emoji;

class RoomController extends Controller
{
        public function index () {
        $data = [
            'user' => Auth::user(),
            'rooms' => Chatroom::where('private_room_id', null)->get(),
            'emojis' => Emoji::all(),
            'appName' => config('app.name'),
            'confettiWords' => env('APP_CONFETTI_WORDS')
        ];
        return view('rooms.index', ['data' => $data]);
    }

    public function oneRoom(Request $request, $roomId)
            {
                $data = [
                    'user' => Auth::user(),
                    'rooms' => Chatroom::where('private_room_id', null)->get(),
                    'emojis' => Emoji::all(),
                    'appName' => config('app.name'),
                    'confettiWords' => env('APP_CONFETTI_WORDS')
                ];

                return view('chat.index', [
                    'data' => $data,
                    'roomId' => $roomId
                ]);
            }
}
