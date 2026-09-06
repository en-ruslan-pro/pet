<?php

namespace Database\Factories;

use App\Models\Character;
use App\Models\PetAction;
use App\Models\PetActionExecution;
use App\Models\PetBalanceVersion;
use App\Models\PetModel;
use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetActionExecution>
 */
class PetActionExecutionFactory extends Factory
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
            'pet_action_id' => PetAction::factory(),
            'pet_balance_version_id' => PetBalanceVersion::factory(),
            'action_key' => 'idle',
            'source' => 'autonomous',
            'status' => 'requested',
            'requested_at' => now(),
            'configuration_snapshot' => ['settings' => ['need_effects' => []]],
            'needs_before' => ['satiety' => 80, 'energy' => 80, 'happiness' => 80],
        ];
    }
}
