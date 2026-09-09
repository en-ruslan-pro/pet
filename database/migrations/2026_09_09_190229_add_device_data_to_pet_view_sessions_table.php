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
        Schema::table('pet_view_sessions', function (Blueprint $table): void {
            $table->json('device_data')->nullable()->after('client_session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pet_view_sessions', function (Blueprint $table): void {
            $table->dropColumn('device_data');
        });
    }
};
