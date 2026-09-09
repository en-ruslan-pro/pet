<?php

namespace App\Console\Commands\Pet;

use App\Models\PetActionExecution;
use App\Services\RoomCommandDispatcher;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('pet:retry-pending-room-commands')]
#[Description('Retry room commands that could not be delivered to TV')]
class RetryPendingRoomCommands extends Command
{
    public function handle(RoomCommandDispatcher $dispatcher): int
    {
        $retriedCount = 0;

        PetActionExecution::query()
            ->where('source', 'controller')
            ->where('status', 'requested')
            ->where('delivery_status', 'pending')
            ->orderBy('id')
            ->each(function (PetActionExecution $execution) use ($dispatcher, &$retriedCount): void {
                if ($dispatcher->dispatch($execution)) {
                    $retriedCount++;
                }
            });

        $this->info("Retried {$retriedCount} pending room command(s).");

        return self::SUCCESS;
    }
}
