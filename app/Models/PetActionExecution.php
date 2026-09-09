<?php

namespace App\Models;

use Database\Factories\PetActionExecutionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $requested_at
 * @property Carbon|null $started_at
 * @property Carbon|null $finished_at
 * @property Carbon|null $start_deadline_at
 * @property Carbon|null $finish_deadline_at
 * @property array<string, mixed>|null $configuration_snapshot
 * @property array<string, mixed>|null $needs_before
 * @property array<string, mixed>|null $needs_after
 */
class PetActionExecution extends Model
{
    /** @use HasFactory<PetActionExecutionFactory> */
    use HasFactory;

    protected $fillable = ['room_id', 'character_id', 'pet_model_id', 'pet_action_id', 'pet_balance_version_id', 'pet_view_session_id', 'action_key', 'command_action', 'source', 'status', 'delivery_status', 'delivery_attempts', 'delivered_at', 'last_delivery_error', 'requested_at', 'started_at', 'finished_at', 'start_deadline_at', 'finish_deadline_at', 'duration_milliseconds', 'finish_reason', 'configuration_snapshot', 'needs_before', 'needs_after'];

    protected function casts(): array
    {
        return ['requested_at' => 'datetime', 'started_at' => 'datetime', 'finished_at' => 'datetime', 'start_deadline_at' => 'datetime', 'finish_deadline_at' => 'datetime', 'delivered_at' => 'datetime', 'configuration_snapshot' => 'array', 'needs_before' => 'array', 'needs_after' => 'array'];
    }

    /** @return BelongsTo<Room, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** @return BelongsTo<PetViewSession, $this> */
    public function viewSession(): BelongsTo
    {
        return $this->belongsTo(PetViewSession::class, 'pet_view_session_id');
    }

    /** @return BelongsTo<Character, $this> */
    public function character(): BelongsTo
    {
        return $this->belongsTo(Character::class);
    }

    /** @return BelongsTo<PetModel, $this> */
    public function petModel(): BelongsTo
    {
        return $this->belongsTo(PetModel::class);
    }

    /** @return BelongsTo<PetAction, $this> */
    public function petAction(): BelongsTo
    {
        return $this->belongsTo(PetAction::class);
    }

    /** @return BelongsTo<PetBalanceVersion, $this> */
    public function balanceVersion(): BelongsTo
    {
        return $this->belongsTo(PetBalanceVersion::class, 'pet_balance_version_id');
    }
}
