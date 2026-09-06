<?php

namespace App\Events;

use App\Models\Room;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RoomCommandRequested implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public readonly Room $room, public readonly string $action, public readonly ?int $executionId = null) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('room.'.$this->room->code),
        ];
    }

    public function broadcastAs(): string
    {
        return 'room.command.requested';
    }

    /** @return array{action: string, executionId: ?int, petName: string, needs: array{satiety: int, energy: int, happiness: int}} */
    public function broadcastWith(): array
    {
        return [
            'action' => $this->action,
            'executionId' => $this->executionId,
            'petName' => $this->room->pet_name,
            'needs' => $this->room->petNeeds(),
        ];
    }
}
