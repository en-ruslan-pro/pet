<?php

use App\Models\Character;
use App\Models\PetAction;
use App\Services\CharacterAnimationSentryReporter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schedule;

use function Pest\Laravel\mock;

test('reports every missing base action for an incomplete character', function () {
    PetAction::factory()->create(['key' => 'eat']);
    PetAction::factory()->create(['key' => 'sleep']);
    $character = Character::factory()->create(['name' => 'Incomplete adventurer']);

    mock(CharacterAnimationSentryReporter::class)
        ->shouldReceive('report')
        ->once()
        ->withArgs(fn (Character $reportedCharacter, array $missingActionKeys): bool => $reportedCharacter->is($character) && $missingActionKeys === ['eat', 'sleep']);

    $this->artisan('pet:check-character-animations')
        ->expectsOutputToContain('Incomplete adventurer: eat, sleep')
        ->assertExitCode(Command::FAILURE);
});

test('runs the character animation check every hour', function () {
    $event = collect(Schedule::events())
        ->first(fn ($event): bool => str_contains($event->command, 'pet:check-character-animations'));

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('0 * * * *');
});

test('runs the expired action cleanup every minute', function () {
    $event = collect(Schedule::events())
        ->first(fn ($event): bool => str_contains($event->command, 'pet:abandon-expired-actions'));

    expect($event)->not->toBeNull();
    expect($event->expression)->toBe('* * * * *');
});
