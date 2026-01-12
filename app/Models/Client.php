<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    // Mass assignable fields
    protected $fillable = [
        'company_name',
        'owner_name',
        'email',
        'password',
        'phone_number',
        'company_type',
        'company_address',
        'tax',
        'file',
    ];

    // Cast fields to correct data types
    protected $casts = [
        'company_address' => 'array',
        'tax' => 'boolean',
    ];

    // Hide password when returning JSON
    protected $hidden = [
        'password',
    ];

    /*
     |--------------------------------------------------------------------------
     | Relationships
     |--------------------------------------------------------------------------
     */

    public function houses()
    {
        return $this->hasMany(ClientHouse::class);
    }
}
