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
        Schema::table('rooms', function (Blueprint $table): void {
            $table->unsignedTinyInteger('hunger')->default(20)->after('pet_name');
            $table->unsignedTinyInteger('energy')->default(80)->after('hunger');
            $table->unsignedTinyInteger('happiness')->default(80)->after('energy');
            $table->timestamp('pet_needs_updated_at')->nullable()->after('tv_connected_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn(['hunger', 'energy', 'happiness', 'pet_needs_updated_at']);
        });
    }
};
