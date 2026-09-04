<?php

namespace App\Models;

use Database\Factories\PetModelActionStepFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PetModelActionStep extends Model
{
    /** @use HasFactory<PetModelActionStepFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = ['pet_model_action_id', 'pet_animation_step_id', 'position', 'is_available', 'duration_seconds'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['is_available' => 'boolean'];
    }

    /** @return BelongsTo<PetModelAction, $this> */
    public function petModelAction(): BelongsTo
    {
        return $this->belongsTo(PetModelAction::class);
    }

    /** @return BelongsTo<PetAnimationStep, $this> */
    public function animationStep(): BelongsTo
    {
        return $this->belongsTo(PetAnimationStep::class, 'pet_animation_step_id');
    }
}
