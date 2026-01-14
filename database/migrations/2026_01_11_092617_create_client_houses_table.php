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

            $table->string('street_name');
            $table->string('local_code');
            $table->string('village');
            $table->string('house_number');

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
