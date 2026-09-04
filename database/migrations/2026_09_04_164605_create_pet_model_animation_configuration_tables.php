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
        Schema::create('pet_animation_steps', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->string('name', 100);
            $table->timestamps();
        });

        Schema::create('pet_model_animation_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_model_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pet_animation_step_id')->constrained()->restrictOnDelete();
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['pet_model_id', 'pet_animation_step_id'], 'pet_model_animation_steps_model_step_unique');
        });

        Schema::create('pet_model_animation_step_clips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_model_animation_step_id')->constrained()->cascadeOnDelete();
            $table->string('clip_name', 150);
            $table->unsignedSmallInteger('weight')->default(1);
            $table->decimal('playback_rate', 4, 2)->default(1);
            $table->boolean('is_looping')->default(false);
            $table->timestamps();

            $table->unique(['pet_model_animation_step_id', 'clip_name'], 'pet_model_animation_step_clips_step_clip_unique');
        });

        Schema::create('pet_model_action_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_model_action_id')->constrained()->cascadeOnDelete();
            $table->foreignId('pet_animation_step_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('position');
            $table->boolean('is_available')->default(true);
            $table->unsignedSmallInteger('duration_seconds')->nullable();
            $table->timestamps();

            $table->unique(['pet_model_action_id', 'position'], 'pet_model_action_steps_action_position_unique');
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn('enabled_animation_clips');
        });

        Schema::table('pet_model_actions', function (Blueprint $table) {
            $table->dropColumn('animation_clips');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_model_action_steps');
        Schema::dropIfExists('pet_model_animation_step_clips');
        Schema::dropIfExists('pet_model_animation_steps');
        Schema::dropIfExists('pet_animation_steps');

        Schema::table('pet_model_actions', function (Blueprint $table) {
            $table->json('animation_clips')->nullable();
        });

        Schema::table('characters', function (Blueprint $table) {
            $table->json('enabled_animation_clips')->nullable();
        });
    }
};
