<?php

namespace Database\Factories;

use App\Models\PetAnimationStep;
use App\Models\PetModel;
use App\Models\PetModelAnimationStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetModelAnimationStep>
 */
class PetModelAnimationStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pet_model_id' => PetModel::factory(),
            'pet_animation_step_id' => PetAnimationStep::factory(),
            'is_available' => true,
        ];
    }
}
