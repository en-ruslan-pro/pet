<?php

use App\Filament\Pages\PetBalance;
use App\Filament\Resources\PetModels\Schemas\PetModelForm;
use App\Models\PetActionExecution;
use App\Models\PetNeedSnapshot;
use App\Models\Room;
use App\Models\User;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

test('forbids users without the admin role from accessing the Filament panel', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dostup');

    $response->assertForbidden();
});

test('allows users with the admin role to access the Filament panel', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    $response = $this->actingAs($user)->get('/dostup');

    $response->assertOk();
});

test('shows the character catalog settings to administrators', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    $this->actingAs($user)
        ->get('/dostup/characters/create')
        ->assertSee('Название')
        ->assertSee('Имя по умолчанию')
        ->assertSee('3D-модель');
});

test('shows animation steps and game action configuration for a pet model', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));

    $this->actingAs($user)
        ->get('/dostup/pet-models/create')
        ->assertSee('Внутренние шаги и варианты клипов')
        ->assertSee('Игровые действия');
});

test('shows pet balance statistics to administrators', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));
    PetActionExecution::factory()->create([
        'action_key' => 'sleep',
        'status' => 'finished',
        'requested_at' => now()->subSeconds(3),
        'started_at' => now()->subSeconds(2),
        'finished_at' => now(),
        'duration_milliseconds' => 2_000,
        'needs_before' => ['satiety' => 30, 'energy' => 40, 'happiness' => 50],
        'needs_after' => ['satiety' => 40, 'energy' => 45, 'happiness' => 48],
    ]);
    PetNeedSnapshot::factory()->create(['satiety' => 10]);

    $this->actingAs($user)
        ->get(PetBalance::getUrl())
        ->assertSee(__('pet.analytics.title'))
        ->assertSee(__('pet.analytics.actions'))
        ->assertSee(__('pet.analytics.active'))
        ->assertSee(__('pet.analytics.reason'))
        ->assertSee(__('pet.analytics.critical_needs'))
        ->assertSee(__('pet.analytics.average_start_delay'))
        ->assertSee(__('pet.analytics.average_effect'))
        ->assertSee(__('pet.analytics.needs_at_selection'))
        ->assertSee(__('pet.analytics.need_values', ['satiety' => '+10', 'energy' => '+5', 'happiness' => '-2']))
        ->assertSee(__('pet.analytics.critical_samples', ['count' => 1]))
        ->assertSee(__('pet.analytics.history_limit', ['count' => 50]));
});

test('lists need snapshots by ID when their timestamps match', function () {
    $user = User::factory()->create();
    $user->assignRole(Role::findOrCreate('admin'));
    $room = Room::factory()->create();
    $recordedAt = now();
    PetNeedSnapshot::factory()->for($room)->create([
        'reason' => 'action_before_finish',
        'recorded_at' => $recordedAt,
    ]);
    PetNeedSnapshot::factory()->for($room)->create([
        'reason' => 'action_finished',
        'recorded_at' => $recordedAt,
    ]);

    $this->actingAs($user)
        ->get(PetBalance::getUrl())
        ->assertSeeInOrder([
            __('pet.analytics.snapshot_reasons.action_before_finish'),
            __('pet.analytics.snapshot_reasons.action_finished'),
        ]);
});

test('renders pet model form sections one below another', function () {
    $sections = collect(PetModelForm::configure(Schema::make())->getComponents());
    $sectionSpans = $sections
        ->mapWithKeys(fn ($component) => [$component->getHeading() => $component->getColumnSpan()]);
    $animationSteps = $sections
        ->first(fn ($component) => $component->getHeading() === 'Внутренние шаги и варианты клипов')
        ->getDefaultChildComponents();
    $animationStepComponents = collect($animationSteps)
        ->first(fn ($component) => $component->getName() === 'animationSteps')
        ->getDefaultChildComponents();
    $clipsRepeater = collect($animationStepComponents)
        ->first(fn ($component) => $component->getName() === 'clips');
    $gameActions = $sections->first(fn ($component) => $component->getHeading() === 'Игровые действия');
    $steps = collect($gameActions->getDefaultChildComponents())
        ->first(fn ($component) => $component->getName() === 'petModelActions')
        ->getDefaultChildComponents();
    $stepsRepeater = collect($steps)->first(fn ($component) => $component->getName() === 'steps');

    expect($sectionSpans['Модель'])->toBe(['default' => 'full']);
    expect($sectionSpans['Внутренние шаги и варианты клипов'])->toBe(['default' => 'full']);
    expect($sectionSpans['Игровые действия'])->toBe(['default' => 'full']);
    expect($clipsRepeater->getColumnSpan())->toBe(['default' => 'full']);
    expect($stepsRepeater->getColumnSpan())->toBe(['default' => 'full']);
});
