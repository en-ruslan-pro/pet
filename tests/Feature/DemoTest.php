<?php

use Symfony\Component\Process\Process;

test('renders the virtual pet demo', function () {
    $response = $this->get(route('demo'));

    $response
        ->assertOk()
        ->assertSee('Мурка дома')
        ->assertSee('Virtual Pet TV: Мурка живёт в своей комнате')
        ->assertSee('Освещение')
        ->assertSee('Анимация')
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

test('hides scene controls in tv mode', function () {
    $this->get(route('demo', ['tv' => 1]))
        ->assertOk()
        ->assertSee('class="demo-controls" aria-label="Настройка сцены" hidden', false)
        ->assertSee('.demo-controls[hidden]');
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
        'import { findWalkablePath, getRoomItemOverlaps, isWalkablePosition, ROOM_LAYOUT } from "./resources/js/room-layout.js"; const overlappingLayout = { ...ROOM_LAYOUT, assets: [{ id: "chair", position: [0, 0], width: 2, depth: 2 }], objects: [{ id: "table", position: [0.5, 0], width: 2, depth: 2 }] }; const path = findWalkablePath([0, 0.35], [2.8, 1.55]); console.log(JSON.stringify({ overlaps: getRoomItemOverlaps(), detected: getRoomItemOverlaps(overlappingLayout), pathIsWalkable: path.every((point) => isWalkablePosition(point)), hasDiagonalStep: path.slice(1).some((point, index) => point[0] !== path[index][0] && point[1] !== path[index][1]) }));',
    ], base_path());

    $process->mustRun();

    expect(json_decode($process->getOutput(), true, flags: JSON_THROW_ON_ERROR))
        ->toBe([
            'overlaps' => [],
            'detected' => [['chair', 'table']],
            'pathIsWalkable' => true,
            'hasDiagonalStep' => true,
        ]);
});
