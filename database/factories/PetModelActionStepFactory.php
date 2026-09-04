<?php

namespace Database\Factories;

use App\Models\PetAnimationStep;
use App\Models\PetModelAction;
use App\Models\PetModelActionStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetModelActionStep>
 */
class PetModelActionStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pet_model_action_id' => PetModelAction::factory(),
            'pet_animation_step_id' => PetAnimationStep::factory(),
            'position' => 1,
            'is_available' => true,
            'duration_seconds' => null,
        ];
    }
}
