<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\PetAction;
use App\Models\PetModel;
use App\Models\PetModelAction;
use App\Models\PetType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
                'asset_path' => '/models/stripe-the-cat.glb',
                'configuration' => ['scale' => 1],
            ],
        );

        Character::query()->updateOrCreate(
            ['name' => 'Полосатая кошка'],
            [
                'pet_model_id' => $tabby->id,
                'default_name' => 'Мурка',
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

        $this->seedKayKitAdventurers();
    }

    private function seedKayKitAdventurers(): void
    {
        $adventurer = PetType::query()->updateOrCreate(
            ['key' => 'adventurer'],
            [
                'name' => 'Приключенец',
                'needs_configuration' => [
                    'hunger' => ['minimum' => 0, 'maximum' => 100],
                    'energy' => ['minimum' => 0, 'maximum' => 100],
                    'happiness' => ['minimum' => 0, 'maximum' => 100],
                ],
            ],
        );

        $animationClips = $this->kayKitAnimationClips();
        $actions = [];

        foreach ($animationClips as $clip) {
            $actions[$clip] = PetAction::query()->updateOrCreate(
                ['key' => 'kaykit-'.Str::slug($clip)],
                [
                    'name' => $clip,
                    'configuration' => ['category' => 'kaykit'],
                ],
            );
        }

        foreach ([
            'Barbarian' => ['Варвар', 'Варвар'],
            'Knight' => ['Рыцарь', 'Рыцарь'],
            'Mage' => ['Маг', 'Маг'],
            'Rogue' => ['Разбойник', 'Разбойник'],
            'Rogue_Hooded' => ['Разбойник в капюшоне', 'Тень'],
        ] as $fileName => [$modelName, $defaultName]) {
            $model = PetModel::query()->updateOrCreate(
                ['key' => 'kaykit-'.Str::kebab($fileName)],
                [
                    'pet_type_id' => $adventurer->id,
                    'name' => $modelName,
                    'asset_path' => "/models/kaykit-adventurers/{$fileName}.glb",
                    'configuration' => ['scale' => 1],
                ],
            );

            Character::query()->updateOrCreate(
                ['name' => $modelName],
                [
                    'pet_model_id' => $model->id,
                    'default_name' => $defaultName,
                    'enabled_animation_clips' => $animationClips,
                ],
            );

            foreach ($actions as $clip => $action) {
                PetModelAction::query()->updateOrCreate(
                    [
                        'pet_model_id' => $model->id,
                        'pet_action_id' => $action->id,
                    ],
                    [
                        'animation_clips' => ['primary' => [$clip]],
                        'execution_configuration' => null,
                        'interaction_points' => null,
                        'is_available' => true,
                    ],
                );
            }
        }
    }

    /** @return list<string> */
    private function kayKitAnimationClips(): array
    {
        return [
            '1H_Melee_Attack_Chop', '1H_Melee_Attack_Slice_Diagonal', '1H_Melee_Attack_Slice_Horizontal', '1H_Melee_Attack_Stab',
            '1H_Ranged_Aiming', '1H_Ranged_Reload', '1H_Ranged_Shoot', '1H_Ranged_Shooting',
            '2H_Melee_Attack_Chop', '2H_Melee_Attack_Slice', '2H_Melee_Attack_Spin', '2H_Melee_Attack_Spinning', '2H_Melee_Attack_Stab', '2H_Melee_Idle',
            '2H_Ranged_Aiming', '2H_Ranged_Reload', '2H_Ranged_Shoot', '2H_Ranged_Shooting',
            'Block', 'Block_Attack', 'Block_Hit', 'Blocking', 'Cheer', 'Death_A', 'Death_A_Pose', 'Death_B', 'Death_B_Pose',
            'Dodge_Backward', 'Dodge_Forward', 'Dodge_Left', 'Dodge_Right',
            'Dualwield_Melee_Attack_Chop', 'Dualwield_Melee_Attack_Slice', 'Dualwield_Melee_Attack_Stab',
            'Hit_A', 'Hit_B', 'Idle', 'Interact', 'Jump_Full_Long', 'Jump_Full_Short', 'Jump_Idle', 'Jump_Land', 'Jump_Start',
            'Lie_Down', 'Lie_Idle', 'Lie_Pose', 'Lie_StandUp', 'PickUp',
            'Running_A', 'Running_B', 'Running_Strafe_Left', 'Running_Strafe_Right',
            'Sit_Chair_Down', 'Sit_Chair_Idle', 'Sit_Chair_Pose', 'Sit_Chair_StandUp',
            'Sit_Floor_Down', 'Sit_Floor_Idle', 'Sit_Floor_Pose', 'Sit_Floor_StandUp',
            'Spellcast_Long', 'Spellcast_Raise', 'Spellcast_Shoot', 'Spellcasting', 'T-Pose', 'Throw',
            'Unarmed_Idle', 'Unarmed_Melee_Attack_Kick', 'Unarmed_Melee_Attack_Punch_A', 'Unarmed_Melee_Attack_Punch_B', 'Unarmed_Pose', 'Use_Item',
            'Walking_A', 'Walking_B', 'Walking_Backwards', 'Walking_C',
        ];
    }
}
