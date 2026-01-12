<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientHouse extends Model
{
    use HasFactory; 
    
    protected $fillable = [
        'street_name',
        'local_code',
        'village',
        'house_number',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}