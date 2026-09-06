<?php

namespace App\Services;

use App\Models\Room;
use Sentry\State\Scope;

use function Sentry\configureScope;

class RoomCommandSentryContext
{
    public function add(Room $room, string $action, ?int $executionId = null): void
    {
        configureScope(static function (Scope $scope) use ($room, $action, $executionId): void {
            $scope->setTags([
                'pet.command.action' => $action,
                'pet.command.source' => 'controller',
                'pet.command.stage' => 'broadcast',
            ]);
            $scope->setContext('pet_command', array_filter([
                'room_id' => $room->getKey(),
                'character_id' => $room->character_id,
                'execution_id' => $executionId,
            ], static fn (mixed $value): bool => $value !== null));
        });
    }
}
