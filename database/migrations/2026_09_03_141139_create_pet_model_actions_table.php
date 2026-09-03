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
        Schema::create('pet_model_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_model_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pet_action_id')->constrained()->restrictOnDelete();
            $table->json('animation_clips');
            $table->json('execution_configuration')->nullable();
            $table->json('interaction_points')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['pet_model_id', 'pet_action_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_model_actions');
    }
};
