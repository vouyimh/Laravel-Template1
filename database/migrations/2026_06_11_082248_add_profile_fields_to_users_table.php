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
            $table->string('first_name', 100)->nullable()->after('name');
            $table->string('last_name', 100)->nullable()->after('first_name');
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('organization', 255)->nullable()->after('phone');
            $table->string('address', 255)->nullable()->after('organization');
            $table->string('state', 100)->nullable()->after('address');
            $table->string('zip_code', 20)->nullable()->after('state');
            $table->string('country', 100)->nullable()->after('zip_code');
            $table->string('language', 10)->nullable()->after('country');
            $table->string('timezone', 50)->nullable()->after('language');
            $table->string('currency', 10)->nullable()->after('timezone');
            $table->string('avatar_path')->nullable()->after('currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'first_name',
                'last_name',
                'phone',
                'organization',
                'address',
                'state',
                'zip_code',
                'country',
                'language',
                'timezone',
                'currency',
                'avatar_path',
            ]);
        });
    }
};
