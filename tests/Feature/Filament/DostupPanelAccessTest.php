<?php

use App\Filament\Pages\PetBalance;
use App\Filament\Resources\PetModels\Schemas\PetModelForm;
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

    $this->actingAs($user)
        ->get(PetBalance::getUrl())
        ->assertSee(__('pet.analytics.title'))
        ->assertSee(__('pet.analytics.actions'))
        ->assertSee(__('pet.analytics.active'))
        ->assertSee(__('pet.analytics.reason'))
        ->assertSee(__('pet.analytics.history_limit', ['count' => 50]));
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
