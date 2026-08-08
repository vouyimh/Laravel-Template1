<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_house_id',
        'title',
        'description',
        'status',
        'priority',
        'due_date',
        'end_date',
        'started_at',
        'completed_at',
        'start_location',
    ];

    protected $casts = [
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'started_at'     => 'datetime',
        'completed_at'   => 'datetime',
        'due_date'       => 'date',
        'end_date'       => 'date',
        'start_location' => 'array',
    ];

    public function clientHouse(): BelongsTo
    {
        return $this->belongsTo(ClientHouse::class);
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assigned_users');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(TaskFile::class)->latest();
    }

    public function activities(): HasMany
    {
        return $this->hasMany(TaskActivity::class)->latest();
    }

    public function isAssignedTo($userId): bool
    {
        return $this->assignees()->where('users.id', $userId)->exists();
    }
}
