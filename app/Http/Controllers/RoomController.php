<?php

namespace App\Http\Controllers;

use App\Events\RoomCommandRequested;
use App\Models\Character;
use App\Models\PetActionExecution;
use App\Models\PetViewSession;
use App\Models\Room;
use App\Services\PetTelemetryService;
use App\Services\RoomCommandSentryContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class RoomController extends Controller
{
    public function __construct(private RoomCommandSentryContext $sentryContext) {}

    public function create(): View
    {
        return view('rooms.create', [
            'characters' => Character::query()
                ->with('petModel')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request, PetTelemetryService $telemetry): RedirectResponse
    {
        $validated = $request->validate([
            'character_id' => ['required', 'integer', 'exists:characters,id'],
            'pet_name' => ['nullable', 'string', 'max:30'],
        ]);

        $character = Character::query()->whereKey($validated['character_id'])->firstOrFail();
        $room = Room::createForCharacter($character, $validated['pet_name'] ?? null);
        $telemetry->recordRoomCreated($room);
        $this->grantAccess($request, $room);

        return to_route('room.show', $room);
    }

    public function tvEntry(): View
    {
        return view('tv.entry');
    }

    public function enterTv(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'alpha_num', 'size:6'],
        ]);

        $room = Room::query()->where('code', Str::upper($validated['code']))->firstOrFail();

        return to_route('tv.show', $room);
    }

    public function showTv(Request $request, Room $room, PetTelemetryService $telemetry): View
    {
        $this->grantAccess($request, $room);
        $room->refreshPetNeeds();
        $telemetry->recordNeedSnapshot($room, 'sync');
        $room->update(['tv_connected_at' => now()]);
        $room->load([
            'character.petModel.animationSteps.animationStep',
            'character.petModel.animationSteps.clips',
            'character.petModel.petModelActions.petAction',
            'character.petModel.petModelActions.steps.animationStep',
        ]);

        return view('tv.show', [
            'room' => $room,
            'character' => $room->character === null ? null : [
                'assetPath' => $room->character->petModel->asset_path,
                'name' => $room->pet_name,
                'animationConfiguration' => $room->character->petModel->animationConfiguration(),
            ],
            'reverb' => [
                'appKey' => config('broadcasting.connections.reverb.key'),
                'host' => config('broadcasting.connections.reverb.options.host'),
                'port' => (int) config('broadcasting.connections.reverb.options.port'),
                'scheme' => config('broadcasting.connections.reverb.options.scheme'),
            ],
        ]);
    }

    public function show(Request $request, Room $room, PetTelemetryService $telemetry): View
    {
        $this->grantAccess($request, $room);
        $room->refreshPetNeeds();
        $telemetry->recordNeedSnapshot($room, 'sync');

        return view('rooms.show', compact('room'));
    }

    public function heartbeat(Request $request, Room $room, PetTelemetryService $telemetry): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $room->update(['tv_connected_at' => now()]);
        $room->refreshPetNeeds();
        $telemetry->recordNeedSnapshot($room, 'sync');

        return response()->json(['connected' => true]);
    }

    public function status(Request $request, Room $room, PetTelemetryService $telemetry): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $room->refreshPetNeeds();
        $telemetry->recordNeedSnapshot($room, 'sync');

        return response()->json([
            'connected' => $room->fresh()->isTvConnected(),
            'needs' => $room->petNeeds(),
        ]);
    }

    public function sendPetAction(Request $request, Room $room, string $action, PetTelemetryService $telemetry): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $behavior = ['feed' => 'eat', 'play' => 'play', 'sleep' => 'sleep'][$action];
        $room->refreshPetNeeds();
        $telemetry->recordNeedSnapshot($room, 'sync');

        $execution = DB::transaction(function () use ($room, $action, $behavior, $telemetry): PetActionExecution {
            $execution = $telemetry->requestAction($room, $behavior, 'controller');
            $this->dispatchRoomCommand($room, $action, $execution->id);

            return $execution;
        });

        return response()->json([
            'action' => $action,
            'message' => match ($action) {
                'feed' => "{$room->pet_name} идёт к миске.",
                'play' => "{$room->pet_name} идёт играть.",
                'sleep' => "{$room->pet_name} идёт отдыхать.",
                default => throw new \LogicException("Unsupported pet action: {$action}"),
            },
            'needs' => $room->petNeeds(),
            'executionId' => $execution->id,
        ]);
    }

    public function startViewSession(Request $request, Room $room, PetTelemetryService $telemetry): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $validated = $request->validate(['client_session_id' => ['required', 'uuid']]);
        $session = $telemetry->startViewSession($room, $validated['client_session_id']);

        return response()->json(['id' => $session->id]);
    }

    public function heartbeatViewSession(Request $request, Room $room, PetViewSession $session, PetTelemetryService $telemetry): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $telemetry->heartbeatViewSession($room, $session);
        $room->update(['tv_connected_at' => now()]);

        return response()->json(['connected' => true, 'needs' => $room->petNeeds()]);
    }

    public function endViewSession(Request $request, Room $room, PetViewSession $session, PetTelemetryService $telemetry): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $telemetry->endViewSession($room, $session);

        return response()->json(['ended' => true]);
    }

    public function startAutonomousAction(Request $request, Room $room, PetTelemetryService $telemetry): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $validated = $request->validate(['action' => ['required', 'string', 'max:100']]);
        $room->refreshPetNeeds();
        $execution = $telemetry->requestAction($room, $validated['action'], 'autonomous');
        $execution = $telemetry->startAction($room, $execution);

        return response()->json(['id' => $execution->id, 'needs' => $room->petNeeds()]);
    }

    public function startActionExecution(Request $request, Room $room, PetActionExecution $execution, PetTelemetryService $telemetry): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $execution = $telemetry->startAction($room, $execution);

        return response()->json(['id' => $execution->id]);
    }

    public function finishActionExecution(Request $request, Room $room, PetActionExecution $execution, PetTelemetryService $telemetry): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $execution = $telemetry->finishAction($room, $execution);

        return response()->json(['id' => $execution->id, 'needs' => $execution->needs_after ?? $room->petNeeds()]);
    }

    public function sendMeow(Request $request, Room $room): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $this->dispatchRoomCommand($room, 'meow');

        return response()->json([
            'action' => 'meow',
            'message' => "{$room->pet_name} услышит вас на телевизоре.",
        ]);
    }

    private function grantAccess(Request $request, Room $room): void
    {
        $request->session()->put('room-access.'.$room->code, true);
    }

    private function ensureAccess(Request $request, Room $room): void
    {
        abort_unless($request->session()->get('room-access.'.$room->code) === true, 403);
    }

    private function dispatchRoomCommand(Room $room, string $action, ?int $executionId = null): void
    {
        try {
            RoomCommandRequested::dispatch($room, $action, $executionId);
        } catch (Throwable $exception) {
            $this->sentryContext->add($room, $action, $executionId);

            throw $exception;
        }
    }
}
