<?php

use App\Models\Pet;
use App\Models\PetAction;
use App\Models\PetModel;
use App\Models\PetModelAction;
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

    expect($cat->needs_configuration)->toBe([
        'hunger' => ['minimum' => 0, 'maximum' => 100],
        'energy' => ['minimum' => 0, 'maximum' => 100],
        'happiness' => ['minimum' => 0, 'maximum' => 100],
    ]);
    expect($sleep->animation_clips)->toBe(['primary' => ['Sleep', 'Rest']]);
    expect($sleep->execution_configuration)->toBe(['duration_seconds' => [9, 13]]);
    expect($sleep->interaction_points)->toBe(['room_item_key' => 'bed']);
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
        'animation_clips' => ['primary' => ['Dance']],
    ]);

    $configuration->load('petModel', 'petAction');

    expect($configuration->petModel->is($model))->toBeTrue();
    expect($configuration->petAction->is($action))->toBeTrue();
    expect($configuration->is_available)->toBeFalse();
    expect($configuration->animation_clips)->toBe(['primary' => ['Dance']]);
});
