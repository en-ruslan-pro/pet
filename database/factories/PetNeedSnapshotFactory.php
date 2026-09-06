<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\PetActionExecution;
use App\Models\PetBalanceVersion;
use App\Models\PetModel;
use App\Models\PetNeedSnapshot;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetNeedSnapshot>
 */
class PetNeedSnapshotFactory extends Factory
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
            'pet_action_execution_id' => PetActionExecution::factory(),
            'pet_balance_version_id' => PetBalanceVersion::factory(),
            'satiety' => 80,
            'energy' => 80,
            'happiness' => 80,
            'reason' => 'sync',
            'recorded_at' => now(),
        ];
    }
}
