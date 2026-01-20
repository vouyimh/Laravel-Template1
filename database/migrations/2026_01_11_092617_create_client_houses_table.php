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
        Schema::create('client_houses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('client_id')
                ->constrained('clients' , 'client_id')
                ->cascadeOnDelete();

            $table->string('house_address');
            $table->integer('room');
            $table->string('size');
            $table->string('time');
            $table->string('tools');
            $table->string('tasks');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_houses');
    }
};
