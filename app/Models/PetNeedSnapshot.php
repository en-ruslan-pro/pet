<?php

namespace App\Models;

use Database\Factories\PetNeedSnapshotFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/** @property Carbon|null $recorded_at */
class PetNeedSnapshot extends Model
{
    /** @use HasFactory<PetNeedSnapshotFactory> */
    use HasFactory;

    protected $fillable = ['room_id', 'character_id', 'pet_model_id', 'pet_action_execution_id', 'pet_balance_version_id', 'satiety', 'energy', 'happiness', 'reason', 'recorded_at'];

    protected function casts(): array
    {
        return ['recorded_at' => 'datetime'];
    }

    /** @return BelongsTo<Room, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /** @return BelongsTo<PetActionExecution, $this> */
    public function execution(): BelongsTo
    {
        return $this->belongsTo(PetActionExecution::class, 'pet_action_execution_id');
    }

    /** @return BelongsTo<PetBalanceVersion, $this> */
    public function balanceVersion(): BelongsTo
    {
        return $this->belongsTo(PetBalanceVersion::class, 'pet_balance_version_id');
    }
}
