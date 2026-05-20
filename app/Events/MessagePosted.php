<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Message;

class MessagePosted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Message $message;

    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    public function broadcastOn(): array
    {
        $chatroom = $this->message->chatroom;

        if ($chatroom && $chatroom->private_room_id) {
            return [new PrivateChannel('room.' . $chatroom->id)];
        }

        return [new PresenceChannel('room.' . $this->message->room_id)];
    }
}
