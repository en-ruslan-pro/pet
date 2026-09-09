<?php

namespace App\Services;

use App\Events\RoomCommandRequested;
use App\Models\PetActionExecution;
use Illuminate\Support\Facades\DB;
use Throwable;

class RoomCommandDispatcher
{
    public function __construct(private RoomCommandSentryContext $sentryContext) {}

    public function dispatch(PetActionExecution $execution): bool
    {
        $execution = DB::transaction(function () use ($execution): ?PetActionExecution {
            $execution = PetActionExecution::query()->with('room')->lockForUpdate()->findOrFail($execution->id);

            if ($execution->source !== 'controller' || $execution->status !== 'requested' || $execution->delivery_status === 'delivered') {
                return null;
            }

            $execution->forceFill([
                'delivery_status' => 'dispatching',
                'delivery_attempts' => $execution->delivery_attempts + 1,
                'last_delivery_error' => null,
            ])->save();

            return $execution;
        });

        if ($execution === null || $execution->room === null || $execution->command_action === null) {
            return false;
        }

        try {
            RoomCommandRequested::dispatch($execution->room, $execution->command_action, $execution->id);
        } catch (Throwable $exception) {
            $this->sentryContext->add($execution->room, $execution->command_action, $execution->id);
            $execution->forceFill([
                'delivery_status' => 'pending',
                'last_delivery_error' => str($exception->getMessage())->limit(500),
            ])->save();

            throw $exception;
        }

        $execution->forceFill([
            'delivery_status' => 'delivered',
            'delivered_at' => now(),
        ])->save();

        return true;
    }
}
