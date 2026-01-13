<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Staff', function (Blueprint $table) {
            $table->increments('StaffID');
            $table->string('FirstName', 50);
            $table->string('LastName', 50);
            $table->string('Email', 100)->unique();
            $table->enum('Role', ['Temporary', 'Permanent', 'Company']);
            $table->string('Username', 50)->unique();
            $table->string('Password', 255);
            $table->string('PhoneNumber', 20)->nullable();

            // ✅ image path
            $table->string('ProfilePicture', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Staff');
    }
};
