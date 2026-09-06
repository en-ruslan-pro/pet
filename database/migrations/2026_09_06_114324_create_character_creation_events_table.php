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
        Schema::create('character_creation_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('character_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('pet_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pet_name', 30);
            $table->string('configuration_hash', 64)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->index(['character_id', 'created_at']);
            $table->index(['pet_model_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_creation_events');
    }
};
