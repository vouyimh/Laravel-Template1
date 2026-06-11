<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public Task $task,
        public User $assigner
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'          => 'task_assigned',
            'task_id'       => $this->task->id,
            'task_title'    => $this->task->title,
            'task_status'   => $this->task->status,
            'assigner_id'   => $this->assigner->id,
            'assigner_name' => $this->assigner->name,
            'message'       => "{$this->assigner->name} assigned you to task: \"{$this->task->title}\"",
            'url'           => route('tasks.show', $this->task->id),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
