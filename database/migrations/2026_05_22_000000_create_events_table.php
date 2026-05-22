<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start');
            $table->dateTime('end')->nullable();
            $table->boolean('all_day')->default(false);
            $table->string('category', 32)->default('etc'); // personal, business, family, holiday, etc
            $table->string('url')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'start']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
