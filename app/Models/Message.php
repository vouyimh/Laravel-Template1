<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = ['content', 'message_type', 'file_path', 'file_name', 'file_size', 'mime_type', 'metadata', 'room_id', 'user_id'];

    protected $casts = [
        'metadata' => 'json',
        'file_size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function chatroom(): BelongsTo
    {
        return $this->belongsTo(Chatroom::class, 'room_id');
    }

    public function reactions (): HasMany {
        return $this->hasMany(Reaction::class, 'msg_id');
    }
}
