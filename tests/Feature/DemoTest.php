<?php

test('renders the virtual pet demo', function () {
    $response = $this->get(route('demo'));

    $response
        ->assertOk()
        ->assertSee('Мурка дома')
        ->assertSee('Virtual Pet TV: Мурка живёт в своей комнате');
});
