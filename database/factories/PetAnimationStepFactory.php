<?php

namespace Database\Factories;

use App\Models\PetAnimationStep;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetAnimationStep>
 */
class PetAnimationStepFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(3),
            'name' => fake()->words(2, true),
        ];
    }
}
