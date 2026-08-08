<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientHouse extends Model
{
    use HasFactory; 
    
    protected $fillable = [
        'client_id',
        'house_address',
        'room',
        'size',
        'time',
        'tools',
        'tasks',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id', 'client_id');
    }

    public function cleaningTasks()
    {
        return $this->hasMany(Task::class);
    }
}