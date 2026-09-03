<?php

use App\Models\User;
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
        ->assertSee('3D-модель')
        ->assertSee('Включённые анимации');
});
