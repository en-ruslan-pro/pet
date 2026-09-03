<?php

namespace Database\Factories;

use App\Models\PetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetType>
 */
class PetTypeFactory extends Factory
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
            'needs_configuration' => [
                'hunger' => ['minimum' => 0, 'maximum' => 100],
            ],
        ];
    }
}
