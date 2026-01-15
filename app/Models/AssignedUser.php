<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignedUser extends Model
{
     protected $table = 'assigned_users';

    protected $fillable = [
        'task_id',
        'user_id'
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
