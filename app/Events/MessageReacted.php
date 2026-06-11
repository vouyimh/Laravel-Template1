<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageReacted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public int $msg_id;
    public int $user_id;
    public int $emoji_id;
    public $chatroom;

    public function __construct(array $data)
    {
        $this->msg_id   = $data['msg_id'];
        $this->user_id  = $data['user_id'];
        $this->emoji_id = $data['emoji_id'];
        $this->chatroom = $data['chatroom'];
    }

    public function broadcastOn(): array
    {
        if ($this->chatroom && $this->chatroom->private_room_id) {
            return [new PrivateChannel('room.' . $this->chatroom->id)];
        }

        return [new PresenceChannel('room.' . $this->chatroom->id)];
    }

    public function broadcastWith(): array
    {
        return [
            'msg_id'   => $this->msg_id,
            'user_id'  => $this->user_id,
            'emoji_id' => $this->emoji_id,
        ];
    }
}
