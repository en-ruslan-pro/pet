<?php

use App\Events\RoomCommandRequested;
use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;

test('creates a room with a unique connection code', function () {
    $response = $this->post(route('room.store'), [
        'pet_name' => 'Барсик',
    ]);

    $room = Room::query()->sole();

    $response->assertRedirect(route('room.show', $room));
    expect($room->pet_name)->toBe('Барсик');
    expect($room->code)->toMatch('/^[A-Z0-9]{6}$/');
});

test('opens the tv room from its connection code and records the connection', function () {
    $room = Room::factory()->create(['code' => 'TV1234']);

    $response = $this->post(route('tv.enter'), ['code' => 'tv1234']);

    $response->assertRedirect(route('tv.show', $room));

    $this->get(route('tv.show', $room))
        ->assertOk()
        ->assertSee($room->pet_name)
        ->assertSee('data-tv-room', false);

    expect($room->fresh()->isTvConnected())->toBeTrue();
});

test('sends the meow command to the private room channel', function () {
    Event::fake([RoomCommandRequested::class]);
    $room = Room::factory()->create(['code' => 'MEOW01']);

    $this->get(route('room.show', $room));

    $this->postJson(route('room.meow', $room))
        ->assertOk()
        ->assertJsonPath('action', 'meow');

    Event::assertDispatched(RoomCommandRequested::class, function (RoomCommandRequested $event) use ($room): bool {
        return $event->room->is($room) && $event->action === 'meow';
    });
});

test('shows the room code without a qr code on the controller', function () {
    $room = Room::factory()->create(['code' => 'CODE01']);

    $this->get(route('room.show', $room))
        ->assertOk()
        ->assertSee('CODE01')
        ->assertDontSee('data-room-qr', false);
});

test('authorizes the private room channel after the room is opened in the browser session', function () {
    config([
        'broadcasting.default' => 'reverb',
        'broadcasting.connections.reverb.app_id' => 'test-app',
        'broadcasting.connections.reverb.key' => 'test-key',
        'broadcasting.connections.reverb.secret' => 'test-secret',
    ]);
    Broadcast::forgetDrivers();
    require base_path('routes/channels.php');
    $room = Room::factory()->create(['code' => 'AUTH01']);

    $this->get(route('room.show', $room))->assertSessionHas('room-access.AUTH01', true);

    $this->post('/broadcasting/auth', [
        'channel_name' => "private-room.{$room->code}",
        'socket_id' => '1234.5678',
    ])->assertOk()->assertJsonStructure(['auth']);
});

test('does not accept a room command before the room is opened in the browser session', function () {
    $room = Room::factory()->create(['code' => 'SAFE01']);

    $this->postJson(route('room.meow', $room))->assertForbidden();
});
