<?php

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
        ->assertSee('Дальность верхнего света')
        ->assertSee('Свет от камеры')
        ->assertSee('Дальность света от камеры')
        ->assertSee('Сила света от камеры');
});

test('hides scene controls in tv mode', function () {
    $this->get(route('demo', ['tv' => 1]))
        ->assertOk()
        ->assertSee('class="demo-controls" aria-label="Настройка сцены" hidden', false);
});
