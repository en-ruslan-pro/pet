<?php

namespace Database\Factories;

use App\Models\PetAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetAction>
 */
class PetActionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'configuration' => ['category' => 'autonomous'],
        ];
    }
}
