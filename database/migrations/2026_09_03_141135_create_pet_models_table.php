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
        Schema::create('pet_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_type_id')->constrained()->restrictOnDelete();
            $table->string('key', 50)->unique();
            $table->string('name', 100);
            $table->string('asset_path', 255);
            $table->json('configuration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_models');
    }
};
