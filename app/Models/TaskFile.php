<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TaskFile extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'type',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Inline-view URL (streamed via Laravel route — works on Windows without storage:link)
    public function getUrlAttribute(): string
    {
        return route('stafftask.files.show', [$this->task_id, $this->id]);
    }

    // Force-download URL
    public function getDownloadUrlAttribute(): string
    {
        return route('stafftask.files.show', [$this->task_id, $this->id, 'download' => 1]);
    }
}
