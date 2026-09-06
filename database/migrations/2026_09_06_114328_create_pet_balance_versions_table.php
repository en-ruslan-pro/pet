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
        if (! Schema::hasTable('pet_balance_versions')) {
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

        $this->addMissingForeignKey('pet_action_executions', 'pet_action_executions_pet_balance_version_id_foreign');
        $this->addMissingForeignKey('pet_need_snapshots', 'pet_need_snapshots_pet_balance_version_id_foreign');
    }

    private function addMissingForeignKey(string $tableName, string $foreignKeyName): void
    {
        if (! Schema::hasTable($tableName)
            || ! Schema::hasColumn($tableName, 'pet_balance_version_id')
            || Schema::hasForeignKey($tableName, $foreignKeyName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($foreignKeyName) {
            $table->foreign('pet_balance_version_id', $foreignKeyName)
                ->references('id')
                ->on('pet_balance_versions')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->dropForeignKey('pet_need_snapshots', 'pet_need_snapshots_pet_balance_version_id_foreign');
        $this->dropForeignKey('pet_action_executions', 'pet_action_executions_pet_balance_version_id_foreign');

        Schema::dropIfExists('pet_balance_versions');
    }

    private function dropForeignKey(string $tableName, string $foreignKeyName): void
    {
        if (! Schema::hasTable($tableName) || ! Schema::hasForeignKey($tableName, $foreignKeyName)) {
            return;
        }

        Schema::table($tableName, function (Blueprint $table) use ($foreignKeyName) {
            $table->dropForeign($foreignKeyName);
        });
    }
};
