<?php

namespace App\Models;

use Database\Factories\PetModelFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetModel extends Model
{
    /** @use HasFactory<PetModelFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'pet_type_id',
        'key',
        'name',
        'asset_path',
        'configuration',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'configuration' => 'array',
        ];
    }

    /** @return BelongsTo<PetType, $this> */
    public function type(): BelongsTo
    {
        return $this->belongsTo(PetType::class, 'pet_type_id');
    }

    /** @return BelongsToMany<PetAction, $this> */
    public function actions(): BelongsToMany
    {
        return $this->belongsToMany(PetAction::class)
            ->withPivot(['animation_clips', 'execution_configuration', 'interaction_points', 'is_available'])
            ->withTimestamps();
    }

    /** @return HasMany<PetModelAction, $this> */
    public function petModelActions(): HasMany
    {
        return $this->hasMany(PetModelAction::class);
    }

    /** @return HasMany<Pet, $this> */
    public function pets(): HasMany
    {
        return $this->hasMany(Pet::class);
    }

    /** @return list<string> */
    public function animationClipNames(): array
    {
        $clipNames = [];

        foreach ($this->petModelActions()
            ->where('is_available', true)
            ->get() as $action) {
            foreach ($action->animation_clips['primary'] ?? [] as $clip) {
                $clipNames[] = $clip;
            }
        }

        return array_values(array_unique($clipNames));
    }
}
