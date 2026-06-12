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
use App\Notifications\NewPrivateMessageNotification;
use Illuminate\Support\Facades\Log;
use App\Models\Emoji;
use App\Models\PushSubscription;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $messages = Message::with(['user', 'reactions'])
            ->where('room_id', (int) $request->query('room_id', ''))
            ->latest()
            ->paginate(50);

        return $messages;
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|max:2000',
        ]);

        $message = new Message();
        $message->room_id      = $request->input('room_id', -1);
        $message->user_id      = Auth::id();
        $message->content      = trim(strip_tags($request->input('content', '')));
        $message->message_type = 'text';
        $message->save();

        $message->load(['user', 'reactions', 'chatroom']);

        $response = response()->json(['message' => $message]);

        // Broadcast and notify after response is sent — prevents Pusher latency blocking the user
        dispatch(function () use ($message) {
            broadcast(new MessagePosted($message))->toOthers();
            $this->notifyPrivateMessageRecipient($message);
        })->afterResponse();

        return $response;
    }

    public function react(Request $request)
    {
        $user_id  = Auth::id();
        $msg_id   = $request->input('msg_id');
        $emoji_id = $request->input('emoji_id');

        $reaction = Reaction::where(['msg_id' => $msg_id, 'user_id' => $user_id])->first();

        if ($reaction) {
            if ((int) $reaction->emoji_id === (int) $emoji_id) {
                $chatroom = Chatroom::find($reaction->message->room_id);
                $reaction->delete();
            } else {
                $reaction->emoji_id = $emoji_id;
                $reaction->save();
                $reaction->load(['user', 'message.chatroom']);
                $chatroom = $reaction->message->chatroom;
            }
        } else {
            $reaction           = new Reaction();
            $reaction->msg_id   = $msg_id;
            $reaction->user_id  = $user_id;
            $reaction->emoji_id = $emoji_id;
            $reaction->save();

            $reaction->load(['user', 'message.chatroom']);
            $chatroom = $reaction->message->chatroom;
        }

        broadcast(new MessageReacted([
            'msg_id'   => $msg_id,
            'user_id'  => $user_id,
            'emoji_id' => $emoji_id,
            'chatroom' => $chatroom,
        ]))->toOthers();

        return ['success' => true, 'reaction' => $reaction];
    }

    public function startChat(Request $request)
    {
        $receiver_id  = $request->input('receiver_id');
        $current_user = Auth::user();

        $receiver = User::find($receiver_id);
        if (!$receiver) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!$this->isPrivateChatAllowed($current_user, $receiver)) {
            Log::warning('Chat blocked', [
                'current_user_id'   => $current_user->id,
                'current_user_role' => $current_user->role,
                'receiver_id'       => $receiver_id,
                'receiver_role'     => $receiver->role,
            ]);

            return response()->json([
                'error'         => 'You cannot chat with this user',
                'current_role'  => $current_user->role,
                'receiver_role' => $receiver->role,
            ], 403);
        }

        $private_room_id = $current_user->id < $receiver_id
            ? $current_user->id . '-' . $receiver_id
            : $receiver_id . '-' . $current_user->id;

        $chatroom = Chatroom::firstOrCreate(['private_room_id' => $private_room_id]);

        return $chatroom;
    }

    public function getUserListForChat()
    {
        $currentUser = Auth::user();
        $userRole    = strtolower(trim($currentUser->role ?? 'client'));
        $fields      = ['id', 'name', 'email', 'role', 'phone'];

        $query = User::where('id', '!=', $currentUser->id);

        return match ($userRole) {
            'admin'  => $query->get($fields),
            'staff'  => $query->where('role', 'admin')->get($fields),
            'client' => $query->where('role', 'admin')->get($fields),
            default  => collect(),
        };
    }

    public function uploadFile(Request $request)
    {
        $request->validate([
            'file'    => 'required|file|max:51200', // 50MB
            'room_id' => 'required|integer',
        ]);

        $file     = $request->file('file');
        $mimeType = $file->getMimeType();
        $filePath = $file->store('chat_files', 'uploads');

        $messageType = 'file';
        if (str_starts_with($mimeType, 'image/')) {
            $messageType = 'image';
        } elseif (str_starts_with($mimeType, 'video/')) {
            $messageType = 'video';
        } elseif (str_starts_with($mimeType, 'audio/')) {
            $messageType = 'voice';
        }

        $message               = new Message();
        $message->room_id      = $request->input('room_id');
        $message->user_id      = Auth::id();
        $message->content      = $file->getClientOriginalName();
        $message->message_type = $messageType;
        $message->file_path    = $filePath;
        $message->file_name    = $file->getClientOriginalName();
        $message->file_size    = $file->getSize();
        $message->mime_type    = $mimeType;
        $message->metadata     = ['uploaded_at' => now()];
        $message->save();

        $message->load(['user', 'reactions', 'chatroom']);

        $fileUrl  = asset('storage/' . $filePath);
        $response = response()->json(['message' => $message, 'file_url' => $fileUrl]);

        dispatch(function () use ($message) {
            broadcast(new MessagePosted($message))->toOthers();
            $this->notifyPrivateMessageRecipient($message);
        })->afterResponse();

        return $response;
    }

    public function chatByPhone($phoneNumber)
    {
        $currentUser = Auth::user();

        $receiver = User::where('phone', $phoneNumber)->first();
        if (!$receiver) {
            return response()->json(['error' => 'User not found'], 404);
        }

        if (!$this->isPrivateChatAllowed($currentUser, $receiver)) {
            return response()->json([
                'error'         => 'You cannot chat with this user',
                'current_role'  => $currentUser->role,
                'receiver_role' => $receiver->role,
            ], 403);
        }

        $private_room_id = $currentUser->id < $receiver->id
            ? $currentUser->id . '-' . $receiver->id
            : $receiver->id . '-' . $currentUser->id;

        $chatroom = Chatroom::firstOrCreate(['private_room_id' => $private_room_id]);

        return response()->json([
            'chatroom' => $chatroom,
            'receiver' => $receiver,
        ]);
    }

    /**
     * Send a database + broadcast notification to the other participant
     * of a private chat room when a new message arrives.
     */
    private function notifyPrivateMessageRecipient(Message $message): void
    {
        $chatroom = $message->chatroom;
        if (!$chatroom || !$chatroom->private_room_id) {
            return;
        }

        [$id1, $id2] = explode('-', $chatroom->private_room_id);
        $receiverId  = ((int) $id1 === Auth::id()) ? (int) $id2 : (int) $id1;

        $receiver = User::find($receiverId);
        if ($receiver) {
            $receiver->notify(new NewPrivateMessageNotification($message));
            $this->sendWebPush($receiverId, $message);
        }
    }

    private function sendWebPush(int $receiverId, Message $message): void
    {
        $subscriptions = PushSubscription::where('user_id', $receiverId)->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject'    => config('app.vapid_subject'),
                    'publicKey'  => config('app.vapid_public_key'),
                    'privateKey' => config('app.vapid_private_key'),
                ],
            ]);

            $senderName = $message->user->name ?? 'Someone';
            $preview    = $message->message_type === 'text'
                ? mb_strimwidth($message->content, 0, 80, '...')
                : ucfirst($message->message_type) . ' message';

            $payload = json_encode([
                'title' => $senderName,
                'body'  => $preview,
                'url'   => url('/chat'),
            ]);

            foreach ($subscriptions as $sub) {
                $webPush->queueNotification(
                    Subscription::create([
                        'endpoint'        => $sub->endpoint,
                        'keys' => [
                            'p256dh' => $sub->p256dh_key,
                            'auth'   => $sub->auth_token,
                        ],
                    ]),
                    $payload
                );
            }

            foreach ($webPush->flush() as $report) {
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getRequest()->getUri()->__toString())->delete();
                }
            }
        } catch (\Exception $e) {
            Log::error('Web push failed: ' . $e->getMessage());
        }
    }

    private function isPrivateChatAllowed($currentUser, $receiver): bool
    {
        $currentRole  = strtolower(trim($currentUser->role ?? ''));
        $receiverRole = strtolower(trim($receiver->role ?? ''));

        if (empty($currentRole) || empty($receiverRole)) {
            Log::warning('Empty role detected in chat validation', [
                'current_role'  => $currentUser->role,
                'receiver_role' => $receiver->role,
            ]);
            return true;
        }

        return match ($currentRole) {
            'admin'  => true,
            'staff'  => $receiverRole === 'admin',
            'client' => $receiverRole === 'admin',
            default  => true,
        };
    }
}
