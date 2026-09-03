<?php

test('shows the virtual pet landing page', function () {
    $this->get(route('home'))
        ->assertOk()
        ->assertSee('Ваш питомец живёт на телевизоре.')
        ->assertSee('Создать питомца')
        ->assertSee(route('room.create'), false);
});
