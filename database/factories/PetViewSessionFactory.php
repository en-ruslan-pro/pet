<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\PetModel;
use App\Models\PetViewSession;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetViewSession>
 */
class PetViewSessionFactory extends Factory
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
            'character_id' => Character::factory(),
            'pet_model_id' => PetModel::factory(),
            'client_session_id' => fake()->uuid(),
            'started_at' => now()->subMinute(),
            'last_seen_at' => now(),
        ];
    }
}
