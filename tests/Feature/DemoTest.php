<?php

use App\Models\Character;
use App\Models\PetModel;
use Symfony\Component\Process\Process;

test('renders the virtual pet demo', function () {
    $response = $this->get(route('demo'));

    $response
        ->assertOk()
        ->assertSee('Virtual Pet TV: Мурка живёт в своей комнате')
        ->assertDontSee('Virtual Pet TV · Demo')
        ->assertDontSee('Мурка дома')
        ->assertDontSee('id="pet-action"', false)
        ->assertSee('Освещение')
        ->assertSee('Анимация')
        ->assertSee('Персонаж')
        ->assertSee('Положение камеры')
        ->assertSee('Верхний свет')
        ->assertSee('Сила верхнего света')
        ->assertSee('Свет у растения')
        ->assertSee('Дальность света у растения')
        ->assertSee('Сила света у растения')
        ->assertSee('Свет от камеры')
        ->assertSee('Дальность света от камеры')
        ->assertSee('Сила света от камеры')
        ->assertSee('Рассеянный свет')
        ->assertSee('Сила рассеянного света')
        ->assertSee('overflow-y: auto;')
        ->assertSee('scrollbar-color: #dd9a45');
});

test('renders selectable characters for the demo scene', function () {
    $model = PetModel::factory()->create([
        'asset_path' => '/models/kaykit-adventurers/Mage.glb',
    ]);
    $character = Character::factory()->for($model)->create([
        'name' => 'Маг',
        'enabled_animation_clips' => ['Idle', 'Walking_A'],
    ]);

    $this->get(route('demo'))
        ->assertSee('id="demo-character"', false)
        ->assertSee($character->name)
        ->assertSee('data-character', false)
        ->assertSee('Walking_A');
});

test('hides scene controls in tv mode', function () {
    $this->get(route('demo', ['tv' => 1]))
        ->assertOk()
        ->assertSee('class="demo-controls" aria-label="Настройка сцены" hidden', false)
        ->assertSee('.demo-controls[hidden]');
});

test('shows the pet action status only in debug mode', function () {
    $this->get(route('demo', ['debug' => 1]))
        ->assertSee('id="pet-action"', false)
        ->assertSee('Просыпается');
});

test('renders the project information and model license', function () {
    $this->get(route('about'))
        ->assertSee('О проекте')
        ->assertSee('Stripe the Cat')
        ->assertSee('DreamNoms')
        ->assertSee('CC BY 4.0');
});

test('uses lower-cost lighting in tv mode', function () {
    $scene = file_get_contents(resource_path('js/demo.js'));

    expect($scene)
        ->toContain("const isTvMode = sceneParameters.has('tv');")
        ->toContain('renderer.shadowMap.enabled = !isTvMode;')
        ->toContain('castsShadow: !isTvMode')
        ->toContain('plantCornerLight.visible = false;')
        ->toContain('cameraLight.visible = false;');
});

test('excludes the removed crib and generated window from the room scene', function () {
    $scene = file_get_contents(resource_path('js/demo.js'));

    expect($scene)
        ->not->toContain("file: 'simple_single_bed'")
        ->not->toContain('windowFrame')
        ->not->toContain('lookWindow');
});

test('room layout prevents unapproved item intersections and keeps cat routes in the allowed area', function () {
    $process = new Process([
        'node',
        '--input-type=module',
        '--eval',
        'import { findWalkablePath, getBoundaryTurnTarget, getRoomItemOverlaps, isWalkablePosition, ROOM_LAYOUT, simplifyWalkPath, smoothAngle } from "./resources/js/room-layout.js"; const overlappingLayout = { ...ROOM_LAYOUT, assets: [{ id: "chair", position: [0, 0], width: 2, depth: 2 }], objects: [{ id: "table", position: [0.5, 0], width: 2, depth: 2 }] }; const path = findWalkablePath([0, 0.35], [2.8, 1.55]); const boundaryTurnTarget = getBoundaryTurnTarget([ROOM_LAYOUT.bounds.minX + ROOM_LAYOUT.catRadius, 0], ROOM_LAYOUT, () => 0.25); const scratchingPost = ROOM_LAYOUT.objects.find(({ id }) => id === "scratchingPost"); console.log(JSON.stringify({ overlaps: getRoomItemOverlaps(), detected: getRoomItemOverlaps(overlappingLayout), pathIsWalkable: path.every((point) => isWalkablePosition(point)), hasDiagonalStep: path.slice(1).some((point, index) => point[0] !== path[index][0] && point[1] !== path[index][1]), simplifiedPathHasFewerPoints: simplifyWalkPath(path).length < path.length, boundaryTurnMovesInward: boundaryTurnTarget[0] > ROOM_LAYOUT.bounds.minX + ROOM_LAYOUT.catRadius && boundaryTurnTarget[1] < 0, turnsSmoothly: smoothAngle(0, Math.PI / 2, 0.05) > 0 && smoothAngle(0, Math.PI / 2, 0.05) < Math.PI / 2, scratchingPostBlocksCat: scratchingPost.catCanEnter === false }));',
    ], base_path());

    $process->mustRun();

    expect(json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR))
        ->toBe([
            'overlaps' => [],
            'detected' => [['chair', 'table']],
            'pathIsWalkable' => true,
            'hasDiagonalStep' => true,
            'simplifiedPathHasFewerPoints' => true,
            'boundaryTurnMovesInward' => true,
            'turnsSmoothly' => true,
            'scratchingPostBlocksCat' => true,
        ]);
});
