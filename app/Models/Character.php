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
        'enabled_animation_clips',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'enabled_animation_clips' => 'array',
        ];
    }

    /** @return BelongsTo<PetModel, $this> */
    public function petModel(): BelongsTo
    {
        return $this->belongsTo(PetModel::class);
    }

    /** @return HasMany<Room, $this> */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }
}
