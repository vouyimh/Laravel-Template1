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

      public function assignedUsers()
    {
        return $this->hasMany(AssignedUser::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
