<?php

namespace Database\Factories;

use App\Models\PetAction;
use App\Models\PetModel;
use App\Models\PetModelAction;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PetModelAction>
 */
class PetModelActionFactory extends Factory
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
            'pet_action_id' => PetAction::factory(),
            'animation_clips' => ['primary' => ['Idle']],
            'execution_configuration' => ['duration_seconds' => [5, 15]],
            'interaction_points' => ['room_item_key' => 'rug'],
            'is_available' => true,
        ];
    }
}
