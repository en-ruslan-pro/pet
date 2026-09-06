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
        if (! Schema::hasTable('pet_action_executions')) {
            Schema::create('pet_action_executions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('room_id')->constrained()->cascadeOnDelete();
                $table->foreignId('character_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('pet_model_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('pet_action_id')->nullable()->constrained()->nullOnDelete();
                $table->foreignId('pet_balance_version_id')->nullable();
                $table->string('action_key', 100);
                $table->string('source', 20);
                $table->string('status', 20)->default('requested');
                $table->timestamp('requested_at')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('finished_at')->nullable();
                $table->unsignedInteger('duration_milliseconds')->nullable();
                $table->string('finish_reason', 40)->nullable();
                $table->json('configuration_snapshot')->nullable();
                $table->json('needs_before')->nullable();
                $table->json('needs_after')->nullable();
                $table->timestamps();
                $table->index(['pet_model_id', 'action_key', 'requested_at'], 'pet_action_execution_model_action_time_index');
                $table->index(['status', 'requested_at']);
            });
        }

        Schema::table('pet_action_executions', function (Blueprint $table) {
            if (! Schema::hasColumn('pet_action_executions', 'pet_balance_version_id')) {
                $table->foreignId('pet_balance_version_id')->nullable();
            }

            if (! Schema::hasIndex('pet_action_executions', 'pet_action_execution_model_action_time_index')) {
                $table->index(['pet_model_id', 'action_key', 'requested_at'], 'pet_action_execution_model_action_time_index');
            }

            if (! Schema::hasIndex('pet_action_executions', ['status', 'requested_at'])) {
                $table->index(['status', 'requested_at']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_action_executions');
    }
};
