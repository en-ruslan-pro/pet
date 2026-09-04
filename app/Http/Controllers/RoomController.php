<?php

namespace App\Http\Controllers;

use App\Events\RoomCommandRequested;
use App\Models\Character;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function create(): View
    {
        return view('rooms.create', [
            'characters' => Character::query()
                ->with('petModel')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'character_id' => ['required', 'integer', 'exists:characters,id'],
            'pet_name' => ['nullable', 'string', 'max:30'],
        ]);

        $character = Character::query()->whereKey($validated['character_id'])->firstOrFail();
        $room = Room::createForCharacter($character, $validated['pet_name'] ?? null);
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

    public function showTv(Request $request, Room $room): View
    {
        $this->grantAccess($request, $room);
        $room->refreshPetNeeds();
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

    public function show(Request $request, Room $room): View
    {
        $this->grantAccess($request, $room);
        $room->refreshPetNeeds();

        return view('rooms.show', compact('room'));
    }

    public function heartbeat(Request $request, Room $room): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $room->update(['tv_connected_at' => now()]);

        return response()->json(['connected' => true]);
    }

    public function status(Request $request, Room $room): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $room->refreshPetNeeds();

        return response()->json([
            'connected' => $room->fresh()->isTvConnected(),
            'needs' => $room->petNeeds(),
        ]);
    }

    public function sendPetAction(Request $request, Room $room, string $action): JsonResponse
    {
        $this->ensureAccess($request, $room);
        $behavior = ['feed' => 'eat', 'play' => 'play', 'sleep' => 'sleep'][$action];
        $room->load('character.petModel');

        if ($room->character !== null) {
            $availableActions = $room->character->petModel->animationConfiguration();

            abort_unless(isset($availableActions[$behavior]), 422, 'Действие недоступно для выбранной модели.');
        }

        DB::transaction(function () use ($room, $action): void {
            $room->performPetAction($action);
            RoomCommandRequested::dispatch($room, $action);
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
        ]);
    }

    public function sendMeow(Request $request, Room $room): JsonResponse
    {
        $this->ensureAccess($request, $room);
        RoomCommandRequested::dispatch($room, 'meow');

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
}
