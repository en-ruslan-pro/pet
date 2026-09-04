<?php

namespace App\Models;

use Database\Factories\PetModelAnimationStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PetModelAnimationStep extends Model
{
    /** @use HasFactory<PetModelAnimationStepFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['pet_model_id', 'pet_animation_step_id', 'is_available'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_available' => 'boolean'];
    }

    /** @return BelongsTo<PetModel, $this> */
    public function petModel(): BelongsTo
    {
        return $this->belongsTo(PetModel::class);
    }

    /** @return BelongsTo<PetAnimationStep, $this> */
    public function animationStep(): BelongsTo
    {
        return $this->belongsTo(PetAnimationStep::class, 'pet_animation_step_id');
    }

    /** @return HasMany<PetModelAnimationStepClip, $this> */
    public function clips(): HasMany
    {
        return $this->hasMany(PetModelAnimationStepClip::class)->orderBy('clip_name');
    }
}
