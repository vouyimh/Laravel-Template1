<?php

namespace App\Notifications;

use App\Models\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class NewPrivateMessageNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(public Message $message) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        $sender      = $this->message->user;
        $preview     = $this->message->message_type === 'text'
            ? \Str::limit($this->message->content, 60)
            : ucfirst($this->message->message_type) . ' shared';

        return [
            'type'        => 'new_private_message',
            'message_id'  => $this->message->id,
            'room_id'     => $this->message->room_id,
            'sender_id'   => $sender->id,
            'sender_name' => $sender->name,
            'preview'     => $preview,
            'message'     => "{$sender->name} sent you a message: \"{$preview}\"",
            'url'         => route('room', $this->message->room_id),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
