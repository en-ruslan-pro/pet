<?php

namespace App\Models;

use Database\Factories\PetModelActionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetModelAction extends Model
{
    /** @use HasFactory<PetModelActionFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'pet_model_id',
        'pet_action_id',
        'execution_configuration',
        'interaction_points',
        'is_available',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'execution_configuration' => 'array',
            'interaction_points' => 'array',
            'is_available' => 'boolean',
        ];
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

    /** @return HasMany<PetModelActionStep, $this> */
    public function steps(): HasMany
    {
        return $this->hasMany(PetModelActionStep::class)->orderBy('position');
    }
}
