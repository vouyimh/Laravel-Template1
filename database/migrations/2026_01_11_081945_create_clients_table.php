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
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('client_id');

            $table->string('company_name');
            $table->string('owner_name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone_number')->nullable();

            // Company address
            $table->string('company_address')->nullable();

            // Yes / No
            $table->boolean('tax')->default(false);

            // Lockbox Number
            $table->string('lockbox')->nullable();

            // Company Type
            $table->enum('company_type', ['Personal', 'Company']);

            // file name or path
            $table->string('file')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
