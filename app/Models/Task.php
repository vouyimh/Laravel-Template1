<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
        use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function assignees()
{
    return $this->belongsToMany(User::class, 'assigned_users');
}

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
