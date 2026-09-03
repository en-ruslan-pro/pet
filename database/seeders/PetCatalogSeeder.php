<?php

namespace Database\Seeders;

use App\Models\PetAction;
use App\Models\PetModel;
use App\Models\PetModelAction;
use App\Models\PetType;
use Illuminate\Database\Seeder;

class PetCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cat = PetType::query()->updateOrCreate(
            ['key' => 'cat'],
            [
                'name' => 'Кошка',
                'needs_configuration' => [
                    'hunger' => ['minimum' => 0, 'maximum' => 100],
                    'energy' => ['minimum' => 0, 'maximum' => 100],
                    'happiness' => ['minimum' => 0, 'maximum' => 100],
                ],
            ],
        );

        $tabby = PetModel::query()->updateOrCreate(
            ['key' => 'cat-tabby'],
            [
                'pet_type_id' => $cat->id,
                'name' => 'Полосатая кошка',
                'asset_path' => '/models/cat.glb',
                'configuration' => ['scale' => 1],
            ],
        );

        foreach ([
            'idle' => ['Праздно стоит', ['Idle'], ['duration_seconds' => [5, 15]], null],
            'walk' => ['Гуляет', ['Walk'], ['speed' => 1.15], null],
            'sit' => ['Сидит', ['Sit'], ['duration_seconds' => [5, 10]], ['room_item_key' => 'sofa']],
            'sleep' => ['Спит', ['Sleep', 'Rest'], ['duration_seconds' => [9, 13]], ['room_item_key' => 'bed']],
            'eat' => ['Ест', ['Eat', 'Feed'], ['duration_seconds' => [6, 9]], ['room_item_key' => 'food_bowl']],
            'play' => ['Играет', ['Play', 'Jump'], ['duration_seconds' => [6, 9]], ['room_item_key' => 'toy_mouse']],
            'look_window' => ['Смотрит в окно', ['LookWindow', 'Idle'], ['duration_seconds' => [6, 12]], ['room_item_key' => 'window']],
            'scratch' => ['Точит когти', ['Scratch', 'Idle'], ['duration_seconds' => [5, 10]], ['room_item_key' => 'scratching_post']],
            'meow' => ['Мяукает', ['Meow', 'Idle'], ['duration_seconds' => [1, 2]], null],
        ] as $key => [$name, $clips, $executionConfiguration, $interactionPoints]) {
            $action = PetAction::query()->updateOrCreate(
                ['key' => $key],
                [
                    'name' => $name,
                    'configuration' => ['category' => 'autonomous'],
                ],
            );

            PetModelAction::query()->updateOrCreate(
                [
                    'pet_model_id' => $tabby->id,
                    'pet_action_id' => $action->id,
                ],
                [
                    'animation_clips' => ['primary' => $clips],
                    'execution_configuration' => $executionConfiguration,
                    'interaction_points' => $interactionPoints,
                    'is_available' => true,
                ],
            );
        }
    }
}
