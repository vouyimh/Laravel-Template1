<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskStatusChangedNotification extends Notification implements ShouldBroadcast
{
    use Queueable;

    public function __construct(
        public Task   $task,
        public string $oldStatus,
        public User   $changer
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase(object $notifiable): array
    {
        $label = [
            'pending'     => 'Pending',
            'in_progress' => 'In Progress',
            'completed'   => 'Completed',
        ];

        return [
            'type'         => 'task_status_changed',
            'task_id'      => $this->task->id,
            'task_title'   => $this->task->title,
            'old_status'   => $this->oldStatus,
            'new_status'   => $this->task->status,
            'changer_id'   => $this->changer->id,
            'changer_name' => $this->changer->name,
            'message'      => "{$this->changer->name} changed task \"{$this->task->title}\" from {$label[$this->oldStatus] ?? $this->oldStatus} to {$label[$this->task->status] ?? $this->task->status}",
            'url'          => route('tasks.show', $this->task->id),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toDatabase($notifiable));
    }
}
