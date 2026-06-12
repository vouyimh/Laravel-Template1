<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

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

    public function getUrlAttribute(): string
    {
        return Storage::disk('uploads')->url($this->path);
    }

    public function getDownloadUrlAttribute(): string
    {
        return Storage::disk('uploads')->url($this->path);
    }
}
