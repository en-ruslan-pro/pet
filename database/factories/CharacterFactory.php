<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\PetModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Character>
 */
class CharacterFactory extends Factory
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
            'name' => fake()->words(2, true),
            'default_name' => fake()->firstName(),
        ];
    }
}
