<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Increase messages.content from varchar(1000) to text so it matches
     * the 2000-character limit enforced in MessageController::store().
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->text('content')->change();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('content', 1000)->change();
        });
    }
};
