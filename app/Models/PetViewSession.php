<?php

namespace App\Models;

use Database\Factories\PetViewSessionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property Carbon|null $started_at
 * @property Carbon|null $last_seen_at
 * @property Carbon|null $ended_at
 */
class PetViewSession extends Model
{
    /** @use HasFactory<PetViewSessionFactory> */
    use HasFactory;

    protected $fillable = ['room_id', 'character_id', 'pet_model_id', 'client_session_id', 'started_at', 'last_seen_at', 'ended_at'];

    protected function casts(): array
    {
        return ['started_at' => 'datetime', 'last_seen_at' => 'datetime', 'ended_at' => 'datetime'];
    }

    /** @return BelongsTo<Room, $this> */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
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

    public function durationSeconds(): int
    {
        $lastSeenAt = $this->last_seen_at ?? $this->started_at;

        return $lastSeenAt === null || $this->started_at === null ? 0 : (int) $this->started_at->diffInSeconds($lastSeenAt);
    }
}
