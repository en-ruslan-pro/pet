<?php

namespace App\Filament\Pages;

use App\Models\CharacterCreationEvent;
use App\Models\PetActionExecution;
use App\Models\PetNeedSnapshot;
use App\Models\PetViewSession;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class PetBalance extends Page
{
    protected string $view = 'filament.pages.pet-balance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 10;

    public static function getNavigationLabel(): string
    {
        return __('pet.analytics.title');
    }

    public function getTitle(): string
    {
        return __('pet.analytics.title');
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        $from = now()->subDays(7);
        $to = now();
        $creationEvents = CharacterCreationEvent::query()
            ->with('character')
            ->whereBetween('created_at', [$from, $to])
            ->get();
        $viewSessions = PetViewSession::query()
            ->whereBetween('started_at', [$from, $to])
            ->get();
        $executions = PetActionExecution::query()
            ->whereBetween('requested_at', [$from, $to])
            ->orderBy('action_key')
            ->get()
            ->groupBy('action_key');
        $needSnapshots = PetNeedSnapshot::query()
            ->whereBetween('recorded_at', [$from, $to])
            ->latest('recorded_at')
            ->limit(50)
            ->get();
        $actionRows = [];

        foreach ($executions as $key => $rows) {
            $finishedRows = $rows->where('status', 'finished');
            $activeRows = $rows->whereIn('status', ['requested', 'started']);
            $totalMilliseconds = (int) $finishedRows->sum('duration_milliseconds');
            $actionRows[] = [
                'key' => $key,
                'started' => $rows->whereNotNull('started_at')->count(),
                'active' => $activeRows->count(),
                'finished' => $finishedRows->count(),
                'abandoned' => $rows->where('status', 'abandoned')->count(),
                'totalMilliseconds' => $totalMilliseconds,
                'averageMilliseconds' => $finishedRows->isEmpty() ? 0 : (int) round($totalMilliseconds / $finishedRows->count()),
            ];
        }

        return [
            'from' => $from,
            'to' => $to,
            'createdCount' => $creationEvents->count(),
            'creationsByCharacter' => $creationEvents->groupBy(fn (CharacterCreationEvent $event): string => $event->character_id === null ? __('pet.analytics.deleted_character') : $event->character->name),
            'viewSessionCount' => $viewSessions->count(),
            'viewDurationSeconds' => $viewSessions->sum(fn (PetViewSession $session): int => $session->durationSeconds()),
            'actionRows' => $actionRows,
            'needSnapshots' => $needSnapshots,
            'needSnapshotLimit' => 50,
        ];
    }
}
