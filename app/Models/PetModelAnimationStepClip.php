<?php

namespace App\Models;

use Database\Factories\PetModelAnimationStepClipFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetModelAnimationStepClip extends Model
{
    /** @use HasFactory<PetModelAnimationStepClipFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['pet_model_animation_step_id', 'clip_name', 'weight', 'playback_rate', 'is_looping'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['weight' => 'integer', 'playback_rate' => 'float', 'is_looping' => 'boolean'];
    }

    /** @return BelongsTo<PetModelAnimationStep, $this> */
    public function modelStep(): BelongsTo
    {
        return $this->belongsTo(PetModelAnimationStep::class, 'pet_model_animation_step_id');
    }
}
