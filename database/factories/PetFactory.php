<?php

namespace Database\Factories;

use App\Models\Pet;
use App\Models\PetModel;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Pet>
 */
class PetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_id' => Room::factory(),
            'pet_model_id' => PetModel::factory(),
            'name' => fake()->firstName(),
            'needs' => ['hunger' => 20, 'energy' => 80, 'happiness' => 80],
            'attributes' => ['personality' => []],
        ];
    }
}
