<?php

namespace Database\Factories;

use App\Models\PetBalanceVersion;
use App\Models\PetModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetBalanceVersion>
 */
class PetBalanceVersionFactory extends Factory
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
            'configuration_hash' => fake()->sha256(),
            'configuration' => ['need_decay' => []],
            'published_at' => now(),
        ];
    }
}
