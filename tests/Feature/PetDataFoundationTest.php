<?php

use App\Models\Character;
use App\Models\Pet;
use App\Models\PetAction;
use App\Models\PetAnimationStep;
use App\Models\PetModel;
use App\Models\PetModelAction;
use App\Models\PetModelActionStep;
use App\Models\PetModelAnimationStep;
use App\Models\PetModelAnimationStepClip;
use App\Models\PetType;
use App\Models\Room;
use App\Models\RoomItem;
use Database\Seeders\PetCatalogSeeder;

test('seeds the cat model with its supported action configuration', function () {
    $this->seed(PetCatalogSeeder::class);

    $cat = PetType::query()->where('key', 'cat')->sole();
    $tabby = $cat->models()->where('key', 'cat-tabby')->sole();
    $sleep = $tabby->petModelActions()
        ->whereHas('petAction', fn ($query) => $query->where('key', 'sleep'))
        ->sole();
    $idle = $tabby->petModelActions()
        ->whereHas('petAction', fn ($query) => $query->where('key', 'idle'))
        ->sole();
    $character = Character::query()->where('name', 'Полосатая кошка')->sole();

    expect($cat->needs_configuration)->toBe([
        'satiety' => ['minimum' => 0, 'maximum' => 100],
        'energy' => ['minimum' => 0, 'maximum' => 100],
        'happiness' => ['minimum' => 0, 'maximum' => 100],
    ]);
    expect($sleep->execution_configuration)->toBe(['duration_seconds' => [9, 13]]);
    expect($idle->execution_configuration)->toBe(['duration_seconds' => [2.5, 7.5]]);
    expect($sleep->interaction_points)->toBe(['room_item_key' => 'bed']);
    expect(PetAction::query()->orderBy('key')->get()->mapWithKeys(fn (PetAction $action) => [$action->key => $action->configuration])->all())
        ->toBe([
            'eat' => ['category' => 'autonomous', 'is_autonomous' => true, 'autonomous_weight' => 1, 'need_effects' => ['satiety' => 8, 'energy' => 0, 'happiness' => 0]],
            'idle' => ['category' => 'autonomous', 'is_autonomous' => true, 'autonomous_weight' => 0.5, 'need_effects' => ['satiety' => -1, 'energy' => -1, 'happiness' => -4]],
            'look_window' => ['category' => 'autonomous', 'is_autonomous' => true, 'autonomous_weight' => 1, 'need_effects' => ['satiety' => -1, 'energy' => -1, 'happiness' => 1]],
            'meow' => ['category' => 'autonomous', 'is_autonomous' => true, 'autonomous_weight' => 1, 'need_effects' => ['satiety' => -1, 'energy' => -1, 'happiness' => 0]],
            'play' => ['category' => 'autonomous', 'is_autonomous' => true, 'autonomous_weight' => 2, 'need_effects' => ['satiety' => -4, 'energy' => -6, 'happiness' => 8]],
            'scratch' => ['category' => 'autonomous', 'is_autonomous' => true, 'autonomous_weight' => 2, 'need_effects' => ['satiety' => -1, 'energy' => -2, 'happiness' => 2]],
            'sit' => ['category' => 'autonomous', 'is_autonomous' => true, 'autonomous_weight' => 1, 'need_effects' => ['satiety' => -1, 'energy' => 2, 'happiness' => -3]],
            'sleep' => ['category' => 'autonomous', 'is_autonomous' => true, 'autonomous_weight' => 1, 'need_effects' => ['satiety' => -3, 'energy' => 8, 'happiness' => -4]],
            'walk' => ['category' => 'autonomous', 'is_autonomous' => true, 'autonomous_weight' => 1, 'need_effects' => ['satiety' => -3, 'energy' => -5, 'happiness' => 0]],
        ]);
    expect($tabby->animationConfiguration()['sleep']['steps'])->toBe([[
        'key' => 'sleep.loop',
        'durationSeconds' => null,
        'clips' => [
            ['name' => 'Rest', 'weight' => 1, 'playbackRate' => 1.0, 'isLooping' => true],
            ['name' => 'Sleep', 'weight' => 1, 'playbackRate' => 1.0, 'isLooping' => true],
        ],
    ],
    ]);
    expect($character->default_name)->toBe('Мурка');
    expect($character->petModel->is($tabby))->toBeTrue();
});

test('builds an ordered model action configuration from active animation steps', function () {
    $model = PetModel::factory()->create();
    $action = PetAction::factory()->create(['key' => 'sleep']);
    $modelAction = PetModelAction::factory()->for($model)->for($action)->create();
    $start = PetAnimationStep::factory()->create(['key' => 'sleep.start']);
    $loop = PetAnimationStep::factory()->create(['key' => 'sleep.loop']);
    $modelStart = PetModelAnimationStep::factory()->for($model)->for($start, 'animationStep')->create();
    $modelLoop = PetModelAnimationStep::factory()->for($model)->for($loop, 'animationStep')->create();

    PetModelAnimationStepClip::factory()->for($modelStart, 'modelStep')->create([
        'clip_name' => 'Lie_Down',
        'weight' => 2,
        'playback_rate' => 0.75,
        'is_looping' => false,
    ]);
    PetModelAnimationStepClip::factory()->for($modelLoop, 'modelStep')->create([
        'clip_name' => 'Lie_Idle',
        'is_looping' => true,
    ]);
    PetModelActionStep::factory()->for($modelAction)->for($loop, 'animationStep')->create(['position' => 2, 'duration_seconds' => 8]);
    PetModelActionStep::factory()->for($modelAction)->for($start, 'animationStep')->create(['position' => 1]);

    expect($model->animationConfiguration()['sleep']['steps'])->toBe([
        [
            'key' => 'sleep.start',
            'durationSeconds' => null,
            'clips' => [['name' => 'Lie_Down', 'weight' => 2, 'playbackRate' => 0.75, 'isLooping' => false]],
        ],
        [
            'key' => 'sleep.loop',
            'durationSeconds' => 8,
            'clips' => [['name' => 'Lie_Idle', 'weight' => 1, 'playbackRate' => 1.0, 'isLooping' => true]],
        ],
    ]);
});

test('excludes inactive model steps from an action configuration', function () {
    $model = PetModel::factory()->create();
    $action = PetAction::factory()->create(['key' => 'scratch']);
    $modelAction = PetModelAction::factory()->for($model)->for($action)->create();
    $step = PetAnimationStep::factory()->create(['key' => 'scratch.loop']);
    $modelStep = PetModelAnimationStep::factory()->for($model)->for($step, 'animationStep')->create(['is_available' => false]);

    PetModelAnimationStepClip::factory()->for($modelStep, 'modelStep')->create(['clip_name' => 'Scratch']);
    PetModelActionStep::factory()->for($modelAction)->for($step, 'animationStep')->create(['position' => 1]);

    expect($model->animationConfiguration())->toBe([]);
});

test('ignores invalid scalar action settings in an animation configuration', function () {
    $model = PetModel::factory()->create();
    $action = PetAction::factory()->create(['key' => 'sleep', 'configuration' => 'invalid']);
    $modelAction = PetModelAction::factory()->for($model)->for($action)->create([
        'execution_configuration' => 'invalid',
        'interaction_points' => 'invalid',
    ]);
    $step = PetAnimationStep::factory()->create(['key' => 'sleep.loop']);
    $modelStep = PetModelAnimationStep::factory()->for($model)->for($step, 'animationStep')->create();

    PetModelAnimationStepClip::factory()->for($modelStep, 'modelStep')->create(['clip_name' => 'Sleep']);
    PetModelActionStep::factory()->for($modelAction)->for($step, 'animationStep')->create(['position' => 1]);

    expect($model->animationConfiguration()['sleep']['settings'])->toBe([
        'name' => $action->name,
        'targetRoomItemKey' => null,
    ]);
});

test('seeds KayKit adventurers with configured game action sequences', function () {
    $this->seed(PetCatalogSeeder::class);

    $knight = Character::query()->where('name', 'Рыцарь')->sole();

    expect($knight->petModel->asset_path)->toBe('/models/kaykit-adventurers/Knight.glb');
    expect($knight->petModel->animationClipNames())->toHaveCount(17);
    expect($knight->petModel->animationClipNames())->toContain('Idle', 'Walking_A', 'Lie_Down', 'Lie_StandUp');
    expect($knight->petModel->animationConfiguration()['sleep']['steps'])->toHaveCount(3);
    expect(Character::query()->whereHas('petModel.type', fn ($query) => $query->where('key', 'adventurer'))->count())->toBe(5);
});

test('persists a pet and configured room item for a room', function () {
    $room = Room::factory()->create();
    $model = PetModel::factory()->create();
    $pet = Pet::factory()->for($room)->for($model)->create();
    $item = RoomItem::factory()->for($room)->create([
        'key' => 'bed',
        'interaction_points' => ['sleep' => [-2.75, 0, -2.65]],
    ]);

    $room->load('pets.petModel', 'items');

    expect($room->pets)->toHaveCount(1);
    expect($room->pets->sole()->is($pet))->toBeTrue();
    expect($room->pets->sole()->petModel->is($model))->toBeTrue();
    expect($room->items->sole()->is($item))->toBeTrue();
    expect($room->items->sole()->interaction_points)->toBe(['sleep' => [-2.75, 0, -2.65]]);
});

test('maps an action configuration to its pet model and action', function () {
    $model = PetModel::factory()->create();
    $action = PetAction::factory()->create(['key' => 'dance']);
    $configuration = PetModelAction::factory()->for($model)->for($action)->create([
        'is_available' => false,
    ]);

    $configuration->load('petModel', 'petAction');

    expect($configuration->petModel->is($model))->toBeTrue();
    expect($configuration->petAction->is($action))->toBeTrue();
    expect($configuration->is_available)->toBeFalse();
});
