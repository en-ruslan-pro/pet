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
            $table->timestamp('satiety_updated_at')->nullable()->after('pet_needs_updated_at');
            $table->timestamp('energy_updated_at')->nullable()->after('satiety_updated_at');
            $table->timestamp('happiness_updated_at')->nullable()->after('energy_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table): void {
            $table->dropColumn(['satiety_updated_at', 'energy_updated_at', 'happiness_updated_at']);
        });
    }
};
