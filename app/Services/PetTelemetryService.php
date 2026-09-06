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

    public function requestAction(Room $room, string $actionKey, string $source): PetActionExecution
    {
        [$model, $action, $configuration] = $this->actionConfiguration($room, $actionKey);
        $balanceVersion = $this->balanceVersion($model);

        return PetActionExecution::query()->create([
            'room_id' => $room->id,
            'character_id' => $room->character_id,
            'pet_model_id' => $model->id,
            'pet_action_id' => $action->id,
            'pet_balance_version_id' => $balanceVersion->id,
            'action_key' => $actionKey,
            'source' => $source,
            'status' => 'requested',
            'requested_at' => now(),
            'configuration_snapshot' => $configuration,
            'needs_before' => $room->petNeeds(),
        ]);
    }

    public function startAction(Room $room, PetActionExecution $execution): PetActionExecution
    {
        return DB::transaction(function () use ($room, $execution): PetActionExecution {
            $execution = PetActionExecution::query()->lockForUpdate()->findOrFail($execution->id);
            abort_unless($execution->room_id === $room->id, 404);

            if ($execution->status === 'requested') {
                $execution->forceFill(['status' => 'started', 'started_at' => now()])->save();
                $this->recordNeedSnapshot($room, 'action_started', $execution, force: true);
            }

            return $execution;
        });
    }

    public function finishAction(Room $room, PetActionExecution $execution): PetActionExecution
    {
        return DB::transaction(function () use ($room, $execution): PetActionExecution {
            $execution = PetActionExecution::query()->lockForUpdate()->findOrFail($execution->id);
            abort_unless($execution->room_id === $room->id, 404);

            if ($execution->status === 'finished') {
                return $execution;
            }

            abort_unless(in_array($execution->status, ['requested', 'started'], true), 409);
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

    public function abandonExpiredActions(Room $room): int
    {
        return PetActionExecution::query()
            ->whereBelongsTo($room)
            ->whereIn('status', ['requested', 'started'])
            ->where('requested_at', '<', now()->subMinutes(2))
            ->update([
                'status' => 'abandoned',
                'finish_reason' => 'timeout',
                'finished_at' => now(),
                'updated_at' => now(),
            ]);
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

    /** @param array<string, mixed> $configuration */
    private function configurationHash(array $configuration): string
    {
        return hash('sha256', json_encode($configuration, JSON_THROW_ON_ERROR));
    }
}
