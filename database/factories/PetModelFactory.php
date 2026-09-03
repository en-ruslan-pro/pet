<?php

namespace Database\Factories;

use App\Models\PetModel;
use App\Models\PetType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetModel>
 */
class PetModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pet_type_id' => PetType::factory(),
            'key' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'asset_path' => '/models/pets/'.fake()->slug(2).'.glb',
            'configuration' => ['scale' => 1],
        ];
    }
}
