<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('client');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
                    User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password123'), // change to your secure password
            'role' => 'admin',
        ]);

        // Staff
        User::create([
            'name' => 'Staff User',
            'email' => 'staff@example.com',
            'password' => Hash::make('password123'),
            'role' => 'staff',
        ]);

        // Client
        User::create([
            'name' => 'Client User',
            'email' => 'client@example.com',
            'password' => Hash::make('password123'),
            'role' => 'client',
        ]);
        });
    }
};
