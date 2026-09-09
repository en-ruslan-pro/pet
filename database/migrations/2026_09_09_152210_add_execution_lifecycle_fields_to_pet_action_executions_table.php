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
        Schema::table('pet_action_executions', function (Blueprint $table): void {
            $table->foreignId('pet_view_session_id')->nullable()->after('pet_balance_version_id')->constrained()->nullOnDelete();
            $table->timestamp('start_deadline_at')->nullable()->after('requested_at');
            $table->timestamp('finish_deadline_at')->nullable()->after('started_at');
            $table->string('command_action', 100)->nullable()->after('action_key');
            $table->string('delivery_status', 20)->default('not_required')->after('status');
            $table->unsignedSmallInteger('delivery_attempts')->default(0)->after('delivery_status');
            $table->timestamp('delivered_at')->nullable()->after('delivery_attempts');
            $table->string('last_delivery_error', 500)->nullable()->after('delivered_at');
            $table->index(['delivery_status', 'requested_at'], 'pet_action_execution_delivery_status_time_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pet_action_executions', function (Blueprint $table): void {
            $table->dropIndex('pet_action_execution_delivery_status_time_index');
            $table->dropConstrainedForeignId('pet_view_session_id');
            $table->dropColumn([
                'start_deadline_at',
                'finish_deadline_at',
                'command_action',
                'delivery_status',
                'delivery_attempts',
                'delivered_at',
                'last_delivery_error',
            ]);
        });
    }
};
