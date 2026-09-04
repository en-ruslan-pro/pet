<?php

namespace Database\Seeders;

use App\Models\Character;
use App\Models\PetAction;
use App\Models\PetAnimationStep;
use App\Models\PetModel;
use App\Models\PetModelAction;
use App\Models\PetModelAnimationStep;
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

            $modelAction = PetModelAction::query()->updateOrCreate(
                [
                    'pet_model_id' => $tabby->id,
                    'pet_action_id' => $action->id,
                ],
                [
                    'execution_configuration' => $executionConfiguration,
                    'interaction_points' => $interactionPoints,
                    'is_available' => true,
                ],
            );

            $this->configureActionSteps($tabby, $modelAction, [["{$key}.loop", $name, $clips, true, null]]);
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
                ],
            );

            foreach ($this->kayKitActions() as $key => [$name, $steps]) {
                $action = PetAction::query()->updateOrCreate(
                    ['key' => $key],
                    ['name' => $name, 'configuration' => ['category' => 'autonomous']],
                );
                $modelAction = PetModelAction::query()->updateOrCreate(
                    [
                        'pet_model_id' => $model->id,
                        'pet_action_id' => $action->id,
                    ],
                    [
                        'execution_configuration' => null,
                        'interaction_points' => null,
                        'is_available' => true,
                    ],
                );

                $this->configureActionSteps($model, $modelAction, $steps);
            }
        }
    }

    /**
     * @param  list<array{0: string, 1: string, 2: list<string>, 3: bool, 4: ?int}>  $steps
     */
    private function configureActionSteps(PetModel $model, PetModelAction $modelAction, array $steps): void
    {
        foreach ($steps as $position => [$key, $name, $clips, $isLooping, $durationSeconds]) {
            $step = PetAnimationStep::query()->updateOrCreate(['key' => $key], ['name' => $name]);
            $modelStep = PetModelAnimationStep::query()->updateOrCreate(
                ['pet_model_id' => $model->id, 'pet_animation_step_id' => $step->id],
                ['is_available' => true],
            );

            foreach ($clips as $clip) {
                $modelStep->clips()->updateOrCreate(
                    ['clip_name' => $clip],
                    ['weight' => 1, 'playback_rate' => 1, 'is_looping' => $isLooping],
                );
            }

            $modelAction->steps()->updateOrCreate(
                ['position' => $position + 1],
                [
                    'pet_animation_step_id' => $step->id,
                    'is_available' => true,
                    'duration_seconds' => $durationSeconds,
                ],
            );
        }
    }

    /**
     * @return array<string, array{0: string, 1: list<array{0: string, 1: string, 2: list<string>, 3: bool, 4: ?int}>}>
     */
    private function kayKitActions(): array
    {
        return [
            'idle' => ['Ожидает', [['idle.loop', 'Ожидание', ['Idle', 'Unarmed_Idle'], true, null]]],
            'walk' => ['Гуляет', [['walk.loop', 'Прогулка', ['Walking_A', 'Walking_B'], true, null]]],
            'sit' => ['Сидит', [
                ['sit.start', 'Садится', ['Sit_Chair_Down', 'Sit_Floor_Down'], false, null],
                ['sit.loop', 'Сидит', ['Sit_Chair_Idle', 'Sit_Floor_Idle'], true, 8],
                ['sit.finish', 'Встаёт', ['Sit_Chair_StandUp', 'Sit_Floor_StandUp'], false, null],
            ]],
            'sleep' => ['Спит', [
                ['sleep.start', 'Ложится', ['Lie_Down'], false, null],
                ['sleep.loop', 'Спит', ['Lie_Idle', 'Lie_Pose'], true, 10],
                ['sleep.finish', 'Встаёт после сна', ['Lie_StandUp'], false, null],
            ]],
            'play' => ['Играет', [['play.loop', 'Играет', ['Cheer', 'Jump_Idle', 'Spellcasting'], false, null]]],
        ];
    }
}
