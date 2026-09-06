<?php

namespace App\Models;

use Database\Factories\CharacterFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Character extends Model
{
    /** @use HasFactory<CharacterFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'pet_model_id',
        'name',
        'default_name',
    ];

    /** @return BelongsTo<PetModel, $this> */
    public function petModel(): BelongsTo
    {
        return $this->belongsTo(PetModel::class);
    }

    /**
     * @param  array<int, string>  $actionKeys
     * @return array<int, string>
     */
    public function missingBaseActionAnimations(array $actionKeys): array
    {
        if ($this->petModel === null) {
            return $actionKeys;
        }

        return $this->petModel->missingActionAnimations($actionKeys);
    }

    /** @param array<int, string> $actionKeys */
    public function isReadyForSelection(array $actionKeys): bool
    {
        return $this->missingBaseActionAnimations($actionKeys) === [];
    }

    /** @return HasMany<Room, $this> */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
