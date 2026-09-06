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
        Schema::create('pet_balance_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_model_id')->nullable()->constrained()->nullOnDelete();
            $table->string('configuration_hash', 64);
            $table->json('configuration');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->unique(['pet_model_id', 'configuration_hash'], 'pet_balance_version_model_hash_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pet_balance_versions');
    }
};
