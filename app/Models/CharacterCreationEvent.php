<?php

namespace App\Models;

use Database\Factories\CharacterCreationEventFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterCreationEvent extends Model
{
    /** @use HasFactory<CharacterCreationEventFactory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = ['room_id', 'character_id', 'pet_model_id', 'pet_name', 'configuration_hash', 'created_at'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime'];
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
}
