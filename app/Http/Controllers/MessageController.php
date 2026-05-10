<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\Message;
use App\Models\Reaction;
use App\Models\Chatroom;
use App\Models\User;
use App\Events\MessagePosted;
use App\Events\MessageReacted;
use Illuminate\Support\Facades\Log;
use App\Models\Emoji;

class MessageController extends Controller
{
    public function index (Request $request) {
        $messages = Message::with(['user', 'reactions'])
			->where('room_id', (int) $request->query('room_id', ''))
            ->latest()
            ->paginate(50);
        return $messages;
    }

    public function store (Request $request) {
        $request->validate([
            'content' => 'required|max:2000',
        ]);

        $message = new Message();
        $message->room_id = $request->input('room_id', -1);
        $message->user_id = Auth::user()->id;
        $message->content = trim(strip_tags($request->input('content', '')));

        $message->save();

        $loadedMessage = $message->load(['user', 'reactions', 'chatroom']);
        broadcast(new MessagePosted($loadedMessage))->toOthers(); // send to others EXCEPT user who sent this message

        return response()->json(['message' => $loadedMessage]);
    }

    public function react (Request $request) {
        $user_id = Auth::user()->id;
        $reaction = Reaction::where(['msg_id' => $request->input('msg_id'), 'user_id' => $user_id])->first();
        $msg_id = $request->input('msg_id');
        $emoji_id = $request->input('emoji_id');

        if ($reaction) {
            if ($reaction->emoji_id === $request->input('emoji_id')) {
                // If same emoji is clicked, delete the reaction
                $chatroom = Chatroom::where('id', $reaction->message->room_id)->first();
                $reaction->delete();
            } else {
                // Update the emoji_id if different
                $reaction->emoji_id = $emoji_id;

                $reaction->save();

                $loaded = $reaction->load(['user', 'message.chatroom']);
                $chatroom = $loaded->message->chatroom;
            }
        } else {
            // Create new reaction if none exists
            $reaction = new Reaction();
            $reaction->msg_id = $msg_id;
            $reaction->user_id = $user_id;
            $reaction->emoji_id = $emoji_id;

            $reaction->save();

            $loaded = $reaction->load(['user', 'message.chatroom']);
            $chatroom = $loaded->message->chatroom;
        }

        broadcast(new MessageReacted([
            'msg_id' => $msg_id,
            'user_id' => $user_id,
            'emoji_id' => $emoji_id,
            'chatroom' => $chatroom,
        ]))->toOthers();
        

        return ['success' => true, 'reaction' => $reaction];
    }

    public function startChat(Request $request) {
        $receiver_id = $request->input('receiver_id');
        $current_user = Auth::user();
        $current_user_id = $current_user->id;

        // Validate that receiver exists
        $receiver = User::find($receiver_id);
        if (!$receiver) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // Validate that chat is allowed based on roles
        if (!$this->isPrivateChatAllowed($current_user, $receiver)) {
            return response()->json(['error' => 'You cannot chat with this user'], 403);
        }

        // Create private room id, which is a combination of two user ids, pattern is: smaller id-larger id
        $private_room_id = $current_user_id < $receiver_id ? $current_user_id . '-' . $receiver_id : $receiver_id . '-' . $current_user_id;

        // Find chat room by private_room_id if it exists
        $chatroom = Chatroom::where('private_room_id', $private_room_id)->first();

        if ($chatroom) {
            return $chatroom;
        }

        $chatroom = new Chatroom();
        $chatroom->private_room_id = $private_room_id;
        $chatroom->save();

        return $chatroom;
    }

    /**
     * Validate if private chat is allowed between two users based on their roles
     * - Admin: Can chat with anyone (staff or clients)
     * - Staff: Can only chat with admins
     * - Client: Can chat with other clients or admins (NOT staff)
     */
    private function isPrivateChatAllowed($currentUser, $receiver) {
        $currentRole = $currentUser->role;
        $receiverRole = $receiver->role;

        if ($currentRole === 'admin') {
            // Admin can chat with staff and clients
            return in_array($receiverRole, ['staff', 'client']);
        } elseif ($currentRole === 'staff') {
            // Staff can only chat with admins
            return $receiverRole === 'admin';
        } elseif ($currentRole === 'client') {
            // Client can chat with other clients or admins (NOT staff)
            return in_array($receiverRole, ['client', 'admin']);
        }

        return false;
    }

    public function chat() {
        $currentUser = Auth::user();

        // Filter group rooms based on user role
        $rooms = $this->getVisibleRooms($currentUser);

        $data = [
            'user' => $currentUser,
            'rooms' => $rooms,
            'emojis' => Emoji::all(),
            'appName' => config('app.name'),
            'confettiWords' => env('APP_CONFETTI_WORDS')
        ];
        return view('app', ['data' => $data]);
    }

    /**
     * Filter group chat rooms based on user role
     * - Admin: See all group rooms
     * - Staff: See all group rooms
     * - Client: Cannot see any group rooms (empty array)
     */
    private function getVisibleRooms($user) {
        // Clients cannot access group chat rooms
        if ($user->role === 'client') {
            return [];
        }

        // Admin and Staff can see all group rooms (private_room_id is null)
        return Chatroom::where('private_room_id', null)->get();
    }

    /**
     * Get filtered user list for private chat based on current user's role
     * - Admin: Can see all users (staff + clients)
     * - Staff: Can see only admins
     * - Client: Can see other clients + admins (but not staff)
     */
    public function getUserListForChat() {
        $currentUser = Auth::user();
        $userRole = $currentUser->role;

        $query = User::where('id', '!=', $currentUser->id);

        if ($userRole === 'admin') {
            // Admin can see all users
            return $query->get(['id', 'name', 'email', 'role']);
        } elseif ($userRole === 'staff') {
            // Staff can see only admins
            return $query->where('role', 'admin')->get(['id', 'name', 'email', 'role']);
        } elseif ($userRole === 'client') {
            // Client can see other clients and admins (NOT staff)
            return $query->whereIn('role', ['client', 'admin'])->get(['id', 'name', 'email', 'role']);
        }

        return [];
    }
}
