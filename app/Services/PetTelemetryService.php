<?php

namespace App\Services;

use App\Models\CharacterCreationEvent;
use App\Models\PetAction;
use App\Models\PetActionExecution;
use App\Models\PetBalanceVersion;
use App\Models\PetModel;
use App\Models\PetNeedSnapshot;
use App\Models\PetViewSession;
use App\Models\Room;
use Illuminate\Support\Facades\DB;

class PetTelemetryService
{
    private const START_TIMEOUT_SECONDS = 15;

    private const ROUTE_TIMEOUT_SECONDS = 30;

    private const COMPLETION_GRACE_SECONDS = 10;

    public function recordRoomCreated(Room $room): void
    {
        $room->refresh();
        $room->loadMissing('character.petModel');
        $model = $room->character?->petModel;

        CharacterCreationEvent::query()->create([
            'room_id' => $room->id,
            'character_id' => $room->character_id,
            'pet_model_id' => $model?->id,
            'pet_name' => $room->pet_name,
            'configuration_hash' => $model === null ? null : $this->configurationHash($model->animationConfiguration()),
            'created_at' => $room->created_at ?? now(),
        ]);

        $this->recordNeedSnapshot($room, 'created', force: true);
    }

    public function startViewSession(Room $room, string $clientSessionId): PetViewSession
    {
        $room->loadMissing('character.petModel');

        return PetViewSession::query()->firstOrCreate(
            ['client_session_id' => $clientSessionId],
            [
                'room_id' => $room->id,
                'character_id' => $room->character_id,
                'pet_model_id' => $room->character?->petModel?->id,
                'started_at' => now(),
                'last_seen_at' => now(),
            ],
        );
    }

    public function heartbeatViewSession(Room $room, PetViewSession $session): void
    {
        abort_unless($session->room_id === $room->id, 404);

        $session->forceFill(['last_seen_at' => now()])->save();
        $room->refreshPetNeeds();
        $this->recordNeedSnapshot($room, 'sync');
        $this->abandonExpiredActions($room);
    }

    public function endViewSession(Room $room, PetViewSession $session): void
    {
        abort_unless($session->room_id === $room->id, 404);

        $session->forceFill([
            'last_seen_at' => now(),
            'ended_at' => now(),
        ])->save();
    }

    public function requestAction(Room $room, string $actionKey, string $source, ?string $commandAction = null): PetActionExecution
    {
        [$model, $action, $configuration] = $this->actionConfiguration($room, $actionKey);
        $balanceVersion = $this->balanceVersion($model);
        $requestedAt = now();

        if ($source === 'controller') {
            PetActionExecution::query()
                ->whereBelongsTo($room)
                ->where('source', 'autonomous')
                ->whereIn('status', ['requested', 'started'])
                ->update([
                    'status' => 'abandoned',
                    'finish_reason' => 'interrupted_by_controller',
                    'finished_at' => $requestedAt,
                    'updated_at' => $requestedAt,
                ]);
        }

        return PetActionExecution::query()->create([
            'room_id' => $room->id,
            'character_id' => $room->character_id,
            'pet_model_id' => $model->id,
            'pet_action_id' => $action->id,
            'pet_balance_version_id' => $balanceVersion->id,
            'action_key' => $actionKey,
            'command_action' => $commandAction,
            'source' => $source,
            'status' => 'requested',
            'delivery_status' => $source === 'controller' ? 'pending' : 'not_required',
            'requested_at' => $requestedAt,
            'start_deadline_at' => $requestedAt->copy()->addSeconds(self::START_TIMEOUT_SECONDS),
            'configuration_snapshot' => $configuration,
            'needs_before' => $room->petNeeds(),
        ]);
    }

    public function startAction(Room $room, PetActionExecution $execution, PetViewSession $session): PetActionExecution
    {
        return DB::transaction(function () use ($room, $execution, $session): PetActionExecution {
            $execution = PetActionExecution::query()->lockForUpdate()->findOrFail($execution->id);
            abort_unless($execution->room_id === $room->id, 404);
            abort_unless($session->room_id === $room->id && $session->ended_at === null, 409);
            abort_if($execution->status === 'requested' && $execution->start_deadline_at?->isPast(), 409);
            abort_unless($execution->pet_view_session_id === null || $execution->pet_view_session_id === $session->id, 409);

            if ($execution->status === 'requested') {
                $startedAt = now();
                $execution->forceFill([
                    'status' => 'started',
                    'pet_view_session_id' => $session->id,
                    'started_at' => $startedAt,
                    'finish_deadline_at' => $startedAt->copy()->addSeconds($this->completionTimeoutSeconds($execution->configuration_snapshot)),
                ])->save();
            }

            return $execution;
        });
    }

    public function finishAction(Room $room, PetActionExecution $execution, PetViewSession $session): PetActionExecution
    {
        return DB::transaction(function () use ($room, $execution, $session): PetActionExecution {
            $execution = PetActionExecution::query()->lockForUpdate()->findOrFail($execution->id);
            abort_unless($execution->room_id === $room->id, 404);
            abort_unless($execution->pet_view_session_id === $session->id, 409);

            if ($execution->status === 'finished') {
                return $execution;
            }

            abort_unless($execution->status === 'started', 409);
            abort_if($execution->finish_deadline_at?->isPast(), 409);
            $room = Room::query()->lockForUpdate()->findOrFail($room->id);
            $room->refreshPetNeeds();
            $this->recordNeedSnapshot($room, 'action_before_finish', $execution, force: true);

            $effects = data_get($execution->configuration_snapshot, 'settings.need_effects', []);
            $room->applyNeedEffects(is_array($effects) ? $effects : []);
            $finishedAt = now();
            $duration = $execution->started_at === null ? null : (int) $execution->started_at->diffInMilliseconds($finishedAt);

            $execution->forceFill([
                'status' => 'finished',
                'started_at' => $execution->started_at ?? $finishedAt,
                'finished_at' => $finishedAt,
                'duration_milliseconds' => $duration,
                'finish_reason' => 'completed',
                'needs_after' => $room->petNeeds(),
            ])->save();
            $this->recordNeedSnapshot($room, 'action_finished', $execution, force: true);

            return $execution;
        });
    }

    public function abandonExpiredActions(?Room $room = null): int
    {
        $query = PetActionExecution::query()->where(function ($query): void {
            $query
                ->where(fn ($query) => $query->where('status', 'requested')->where('start_deadline_at', '<', now()))
                ->orWhere(fn ($query) => $query->where('status', 'started')->where('finish_deadline_at', '<', now()));
        });

        if ($room !== null) {
            $query->whereBelongsTo($room);
        }

        return $query
            ->update([
                'status' => 'abandoned',
                'finish_reason' => 'timeout',
                'finished_at' => now(),
                'updated_at' => now(),
            ]);
    }

    public function clearAnalytics(): void
    {
        DB::transaction(function (): void {
            PetNeedSnapshot::query()->delete();
            PetActionExecution::query()->delete();
            PetViewSession::query()->delete();
            CharacterCreationEvent::query()->delete();
            PetBalanceVersion::query()->delete();
        });
    }

    public function recordNeedSnapshot(Room $room, string $reason, ?PetActionExecution $execution = null, bool $force = false): ?PetNeedSnapshot
    {
        if (! $force && $reason === 'sync') {
            $lastSnapshotAt = PetNeedSnapshot::query()
                ->whereBelongsTo($room)
                ->where('reason', 'sync')
                ->latest('recorded_at')
                ->value('recorded_at');

            if ($lastSnapshotAt !== null && now()->diffInMinutes($lastSnapshotAt) < 5) {
                return null;
            }
        }

        $room->loadMissing('character.petModel');
        $needs = $room->petNeeds();

        return PetNeedSnapshot::query()->create([
            'room_id' => $room->id,
            'character_id' => $room->character_id,
            'pet_model_id' => $room->character?->petModel?->id,
            'pet_action_execution_id' => $execution?->id,
            'pet_balance_version_id' => $execution?->pet_balance_version_id,
            'satiety' => $needs['satiety'],
            'energy' => $needs['energy'],
            'happiness' => $needs['happiness'],
            'reason' => $reason,
            'recorded_at' => now(),
        ]);
    }

    /** @return array{PetModel, PetAction, array<string, mixed>} */
    private function actionConfiguration(Room $room, string $actionKey): array
    {
        $room->loadMissing('character.petModel');
        $model = $room->character?->petModel;
        abort_unless($model instanceof PetModel, 422, 'Action is unavailable for this character.');
        $configuration = $model->animationConfiguration();
        abort_unless(isset($configuration[$actionKey]), 422, 'Action is unavailable for this character.');

        $action = PetAction::query()->where('key', $actionKey)->firstOrFail();

        return [$model, $action, $configuration[$actionKey]];
    }

    private function balanceVersion(PetModel $model): PetBalanceVersion
    {
        $configuration = [
            'need_decay' => Room::NEED_DECAY,
            'actions' => $model->animationConfiguration(),
        ];
        $hash = $this->configurationHash($configuration);

        return PetBalanceVersion::query()->firstOrCreate(
            ['pet_model_id' => $model->id, 'configuration_hash' => $hash],
            ['configuration' => $configuration, 'published_at' => now()],
        );
    }

    /** @param array<string, mixed>|null $configuration */
    private function completionTimeoutSeconds(?array $configuration): int
    {
        $stepDuration = 0.0;
        $steps = data_get($configuration, 'steps', []);

        if (is_array($steps)) {
            foreach ($steps as $step) {
                if (is_array($step) && is_numeric($step['durationSeconds'] ?? null)) {
                    $stepDuration += (float) $step['durationSeconds'];
                }
            }
        }

        $configuredDuration = 0.0;
        $configuredDurations = data_get($configuration, 'settings.duration_seconds', []);

        if (is_array($configuredDurations)) {
            foreach ($configuredDurations as $duration) {
                if (is_numeric($duration)) {
                    $configuredDuration = max($configuredDuration, (float) $duration);
                }
            }
        }

        $routeTimeout = filled(data_get($configuration, 'settings.targetRoomItemKey')) ? self::ROUTE_TIMEOUT_SECONDS : 0;

        return (int) ceil(max($stepDuration, $configuredDuration) + $routeTimeout + self::COMPLETION_GRACE_SECONDS);
    }

    /** @param array<string, mixed> $configuration */
    private function configurationHash(array $configuration): string
    {
        return hash('sha256', json_encode($configuration, JSON_THROW_ON_ERROR));
    }
}
