<?php

namespace Database\Factories;

use App\Models\PetModelAnimationStep;
use App\Models\PetModelAnimationStepClip;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetModelAnimationStepClip>
 */
class PetModelAnimationStepClipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pet_model_animation_step_id' => PetModelAnimationStep::factory(),
            'clip_name' => fake()->unique()->word(),
            'weight' => 1,
            'playback_rate' => 1,
            'is_looping' => false,
        ];
    }
}
