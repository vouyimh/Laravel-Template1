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
        Schema::table('messages', function (Blueprint $table) {
            $table->string('message_type')->default('text')->after('content'); // text, file, image, video, voice
            $table->string('file_path')->nullable()->after('message_type');
            $table->string('file_name')->nullable()->after('file_path');
            $table->bigInteger('file_size')->nullable()->after('file_name');
            $table->string('mime_type')->nullable()->after('file_size');
            $table->json('metadata')->nullable()->after('mime_type'); // duration, dimensions, etc.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['message_type', 'file_path', 'file_name', 'file_size', 'mime_type', 'metadata']);
        });
    }
};
