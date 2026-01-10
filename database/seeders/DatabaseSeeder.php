<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\Hash;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
  /**
   * Seed the application's database.
   */
  public function run(): void
  {
    // User::factory(10)->create();

// Admin
User::factory()->create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => Hash::make('password123'),
    'role' => 'admin',
]);

// Staff
User::factory()->create([
    'name' => 'Staff User',
    'email' => 'staff@example.com',
    'password' => Hash::make('password123'),
    'role' => 'staff',
]);

// Client
User::factory()->create([
    'name' => 'Client User',
    'email' => 'client@example.com',
    'password' => Hash::make('password123'),
    'role' => 'client',
]);
    }
}