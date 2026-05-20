<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
    ];

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'assigned_users');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
