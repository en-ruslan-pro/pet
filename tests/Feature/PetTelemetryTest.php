<?php

use App\Events\RoomCommandRequested;
use App\Models\Character;
use App\Models\PetActionExecution;
use App\Models\PetNeedSnapshot;
use App\Models\PetViewSession;
use App\Models\Room;
use Database\Seeders\PetCatalogSeeder;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

test('records an immutable creation event for a new character room', function () {
    $character = Character::factory()->create(['default_name' => 'Луна']);

    $this->post(route('room.store'), ['character_id' => $character->id]);

    $room = Room::query()->sole();
    $this->assertDatabaseHas('character_creation_events', [
        'room_id' => $room->id,
        'character_id' => $character->id,
        'pet_name' => 'Луна',
    ]);
    $this->assertDatabaseHas('pet_need_snapshots', [
        'room_id' => $room->id,
        'reason' => 'created',
    ]);
});

test('records and closes a TV view session for an opened room', function () {
    $room = Room::factory()->create(['code' => 'VIEW01']);
    $clientSessionId = (string) Str::uuid();

    $this->get(route('tv.show', $room));
    $response = $this->postJson(route('tv.sessions.start', $room), ['client_session_id' => $clientSessionId]);

    $response->assertOk()->assertJsonStructure(['id']);
    $session = PetViewSession::query()->sole();
    $this->postJson(route('tv.sessions.heartbeat', [$room, $session]))->assertOk();
    $this->postJson(route('tv.sessions.end', [$room, $session]))->assertOk()->assertJsonPath('ended', true);

    expect($session->fresh()->ended_at)->not->toBeNull();
});

test('applies a configured action effect once after its TV completion', function () {
    Event::fake([RoomCommandRequested::class]);
    $this->seed(PetCatalogSeeder::class);
    $character = Character::query()->where('name', 'Полосатая кошка')->sole();
    $room = Room::factory()->for($character)->create([
        'code' => 'TRACK1',
        'hunger' => 65,
        'energy' => 70,
        'happiness' => 60,
        'pet_needs_updated_at' => now(),
    ]);

    $this->get(route('room.show', $room));
    $this->get(route('tv.show', $room));
    $session = $this->postJson(route('tv.sessions.start', $room), ['client_session_id' => (string) Str::uuid()]);
    $this->postJson(route('room.actions', [$room, 'feed']))
        ->assertOk()
        ->assertJsonPath('needs.satiety', 35);

    $execution = PetActionExecution::query()->sole();
    expect($execution->status)->toBe('requested');
    $this->assertDatabaseHas('rooms', ['id' => $room->id, 'hunger' => 65]);

    $this->postJson(route('tv.actions.execution.start', [$room, $execution]), ['view_session_id' => $session->json('id')])->assertOk();
    $this->postJson(route('tv.actions.execution.finish', [$room, $execution]), ['view_session_id' => $session->json('id')])
        ->assertOk()
        ->assertJsonPath('needs.satiety', 45);
    $this->postJson(route('tv.actions.execution.finish', [$room, $execution]), ['view_session_id' => $session->json('id')])
        ->assertOk()
        ->assertJsonPath('needs.satiety', 45);

    expect($execution->fresh())
        ->status->toBe('finished')
        ->duration_milliseconds->not->toBeNull();
    $this->assertDatabaseHas('rooms', ['id' => $room->id, 'hunger' => 55]);
    $this->assertDatabaseHas('pet_need_snapshots', [
        'pet_action_execution_id' => $execution->id,
        'reason' => 'action_finished',
        'satiety' => 45,
    ]);
    expect(PetNeedSnapshot::query()->where('pet_action_execution_id', $execution->id)->count())->toBe(2);
});

test('rejects a completion that was not started by the TV', function () {
    Event::fake([RoomCommandRequested::class]);
    $this->seed(PetCatalogSeeder::class);
    $character = Character::query()->where('name', 'Полосатая кошка')->sole();
    $room = Room::factory()->for($character)->create(['code' => 'START1']);

    $this->get(route('room.show', $room));
    $this->get(route('tv.show', $room));
    $session = $this->postJson(route('tv.sessions.start', $room), ['client_session_id' => (string) Str::uuid()]);
    $this->postJson(route('room.actions', [$room, 'feed']))->assertOk();
    $execution = PetActionExecution::query()->sole();

    $this->postJson(route('tv.actions.execution.finish', [$room, $execution]), ['view_session_id' => $session->json('id')])->assertConflict();

    expect($execution->fresh()->status)->toBe('requested');
});

test('abandons an unconfirmed action without applying its effect', function () {
    Event::fake([RoomCommandRequested::class]);
    $this->seed(PetCatalogSeeder::class);
    $character = Character::query()->where('name', 'Полосатая кошка')->sole();
    $room = Room::factory()->for($character)->create([
        'code' => 'STALE1',
        'hunger' => 65,
        'pet_needs_updated_at' => now(),
    ]);

    $this->get(route('tv.show', $room));
    $session = $this->postJson(route('tv.sessions.start', $room), ['client_session_id' => (string) Str::uuid()]);
    $this->postJson(route('room.actions', [$room, 'feed']))->assertOk();
    $this->travel(3)->minutes();

    $this->postJson(route('tv.sessions.heartbeat', [$room, $session->json('id')]))->assertOk();

    $this->assertDatabaseHas('pet_action_executions', ['status' => 'abandoned', 'finish_reason' => 'timeout']);
    $this->assertDatabaseHas('rooms', ['id' => $room->id, 'hunger' => 65]);
});

test('abandons a started sleep action after its configured completion deadline', function () {
    Event::fake([RoomCommandRequested::class]);
    $this->seed(PetCatalogSeeder::class);
    $character = Character::query()->where('name', 'Полосатая кошка')->sole();
    $room = Room::factory()->for($character)->create([
        'code' => 'SLEEP1',
        'hunger' => 65,
        'energy' => 70,
        'pet_needs_updated_at' => now(),
    ]);

    $this->get(route('tv.show', $room));
    $session = $this->postJson(route('tv.sessions.start', $room), ['client_session_id' => (string) Str::uuid()]);
    $this->postJson(route('room.actions', [$room, 'sleep']))->assertOk();
    $execution = PetActionExecution::query()->sole();
    $this->postJson(route('tv.actions.execution.start', [$room, $execution]), ['view_session_id' => $session->json('id')])->assertOk();

    $this->travel(54)->seconds();
    $this->postJson(route('tv.sessions.heartbeat', [$room, $session->json('id')]))->assertOk();

    $this->assertDatabaseHas('pet_action_executions', [
        'id' => $execution->id,
        'status' => 'abandoned',
        'finish_reason' => 'timeout',
    ]);
    $this->assertDatabaseHas('rooms', ['id' => $room->id, 'hunger' => 65, 'energy' => 70]);
});

test('allows only the first TV session to start and finish an action', function () {
    Event::fake([RoomCommandRequested::class]);
    $this->seed(PetCatalogSeeder::class);
    $character = Character::query()->where('name', 'Полосатая кошка')->sole();
    $room = Room::factory()->for($character)->create(['code' => 'TVLOCK']);

    $this->get(route('tv.show', $room));
    $firstSession = $this->postJson(route('tv.sessions.start', $room), ['client_session_id' => (string) Str::uuid()]);
    $secondSession = $this->postJson(route('tv.sessions.start', $room), ['client_session_id' => (string) Str::uuid()]);
    $this->postJson(route('room.actions', [$room, 'feed']))->assertOk();
    $execution = PetActionExecution::query()->sole();

    $this->postJson(route('tv.actions.execution.start', [$room, $execution]), ['view_session_id' => $firstSession->json('id')])->assertOk();
    $this->postJson(route('tv.actions.execution.start', [$room, $execution]), ['view_session_id' => $secondSession->json('id')])->assertConflict();
    $this->postJson(route('tv.actions.execution.finish', [$room, $execution]), ['view_session_id' => $secondSession->json('id')])->assertConflict();
    $this->postJson(route('tv.actions.execution.finish', [$room, $execution]), ['view_session_id' => $firstSession->json('id')])->assertOk();
});

test('forbids telemetry before the TV room is opened in the browser session', function () {
    $room = Room::factory()->create(['code' => 'SAFE02']);

    $this->postJson(route('tv.sessions.start', $room), ['client_session_id' => (string) Str::uuid()])->assertForbidden();
});

test('abandons expired actions when the scheduled command runs without a TV heartbeat', function () {
    $execution = PetActionExecution::factory()->create([
        'status' => 'requested',
        'requested_at' => now()->subSeconds(46),
        'start_deadline_at' => now()->subSecond(),
    ]);

    $this->artisan('pet:abandon-expired-actions')
        ->expectsOutput('Abandoned 1 expired pet action(s).')
        ->assertSuccessful();

    expect($execution->fresh())
        ->status->toBe('abandoned')
        ->finish_reason->toBe('timeout');
});
