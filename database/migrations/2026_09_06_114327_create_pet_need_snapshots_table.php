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
        Schema::create('pet_need_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained()->cascadeOnDelete();
            $table->foreignId('character_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pet_model_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pet_action_execution_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pet_balance_version_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('satiety');
            $table->unsignedTinyInteger('energy');
            $table->unsignedTinyInteger('happiness');
            $table->string('reason', 40);
            $table->timestamp('recorded_at')->nullable();
            $table->timestamps();
            $table->index(['pet_model_id', 'recorded_at']);
            $table->index(['room_id', 'recorded_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_need_snapshots');
    }
};
