<?php

use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/** @return array{user_agent: string, platform: string, language: string, screen_width: int, screen_height: int, pixel_ratio: float} */
function tvDevice(): array
{
    return [
        'user_agent' => 'Virtual Pet TV test browser',
        'platform' => 'testOS',
        'language' => 'en',
        'screen_width' => 1920,
        'screen_height' => 1080,
        'pixel_ratio' => 1.0,
    ];
}

/** @param list<'controller'|'tv'> $roles */
function grantRoomAccess(TestCase $test, Room $room, array $roles): void
{
    $test->withSession([
        'room-access.'.$room->code => [
            'roles' => $roles,
            'expires_at' => now()->addDay()->getTimestamp(),
        ],
    ]);
}
