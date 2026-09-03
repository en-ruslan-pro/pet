<?php

use App\Events\RoomCommandRequested;
use App\Models\Room;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastingFactory;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\mock;

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
        ->assertSee('data-tv-room', false)
        ->assertSee('data-pet-needs', false)
        ->assertSee('tv=1');

    expect($room->fresh()->isTvConnected())->toBeTrue();
});

test('opens the tv room directly from the home page with a room code', function () {
    $room = Room::factory()->create(['code' => 'HOME01']);

    $this->get(route('home'))
        ->assertSee('action="'.route('tv.enter').'"', false)
        ->assertSee('name="code"', false);

    $this->post(route('tv.enter'), ['code' => 'home01'])
        ->assertRedirect(route('tv.show', $room));
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

test('updates pet needs and sends the selected care action to the private room channel', function (string $action, array $needs) {
    Event::fake([RoomCommandRequested::class]);
    $room = Room::factory()->create([
        'code' => 'CARE01',
        'hunger' => 65,
        'energy' => 70,
        'happiness' => 60,
        'pet_needs_updated_at' => now(),
    ]);

    $this->get(route('room.show', $room));

    $this->postJson(route('room.actions', [$room, $action]))
        ->assertOk()
        ->assertJsonPath('action', $action)
        ->assertJsonPath('needs', $needs);

    $this->assertDatabaseHas('rooms', [
        'id' => $room->id,
        ...$needs,
    ]);
    Event::assertDispatched(RoomCommandRequested::class, fn (RoomCommandRequested $event): bool => $event->room->is($room) && $event->action === $action);
})->with([
    'feeding' => ['feed', ['hunger' => 35, 'energy' => 70, 'happiness' => 65]],
    'playing' => ['play', ['hunger' => 70, 'energy' => 55, 'happiness' => 80]],
    'sleeping' => ['sleep', ['hunger' => 70, 'energy' => 100, 'happiness' => 60]],
]);

test('refreshes pet needs as time passes', function () {
    $room = Room::factory()->create([
        'code' => 'TIME01',
        'hunger' => 20,
        'energy' => 80,
        'happiness' => 80,
        'pet_needs_updated_at' => now()->subMinutes(30),
    ]);

    $this->get(route('room.show', $room));

    $this->assertDatabaseHas('rooms', [
        'id' => $room->id,
        'hunger' => 26,
        'energy' => 77,
        'happiness' => 78,
    ]);
});

test('forbids pet care commands before the room is opened in the browser session', function () {
    Event::fake([RoomCommandRequested::class]);
    $room = Room::factory()->create(['code' => 'LOCK01']);

    $this->postJson(route('room.actions', [$room, 'sleep']))->assertForbidden();

    Event::assertNotDispatched(RoomCommandRequested::class);
});

test('returns 404 for an unsupported pet care action', function () {
    $room = Room::factory()->create(['code' => 'ACTION1']);

    $this->postJson(route('room.actions', [$room, 'dance']))->assertNotFound();
});

test('does not change pet needs when the realtime command cannot be broadcast', function () {
    $room = Room::factory()->create([
        'code' => 'FAIL01',
        'hunger' => 65,
        'energy' => 70,
        'happiness' => 60,
        'pet_needs_updated_at' => now(),
    ]);
    mock(BroadcastingFactory::class)
        ->shouldReceive('queue')
        ->once()
        ->andThrow(new RuntimeException('Reverb is unavailable.'));

    $this->get(route('room.show', $room));

    $this->postJson(route('room.actions', [$room, 'feed']))->assertServerError();

    $this->assertDatabaseHas('rooms', [
        'id' => $room->id,
        'hunger' => 65,
        'energy' => 70,
        'happiness' => 60,
    ]);
});

test('shows the room code without a qr code on the controller', function () {
    $room = Room::factory()->create(['code' => 'CODE01']);

    $this->get(route('room.show', $room))
        ->assertOk()
        ->assertSee('CODE01')
        ->assertDontSee('data-room-qr', false);
});

test('describes manual TV code entry when creating a room', function () {
    $this->get(route('room.create'))
        ->assertOk()
        ->assertSee('введите код комнаты')
        ->assertDontSee('QR-код');
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
