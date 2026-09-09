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
            $table->dropUnique('pet_view_sessions_client_session_id_unique');
            $table->unique(['room_id', 'client_session_id'], 'pet_view_session_room_client_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pet_view_sessions', function (Blueprint $table): void {
            $table->dropUnique('pet_view_session_room_client_unique');
            $table->unique('client_session_id');
        });
    }
};
