<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([ 'name' => 'Admin User', 'email' => 'admin@example.com', 'password' => Hash::make('password123'), 'role' => 'admin' ]);

User::create([ 'name' => 'Staff User', 'email' => 'staff@example.com', 'password' => Hash::make('password123'), 'role' => 'staff' ]);

User::create([ 'name' => 'Client User', 'email' => 'client@example.com', 'password' => Hash::make('password123'), 'role' => 'client' ]);

    }
}
