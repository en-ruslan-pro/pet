<?php

namespace App\Filament\Pages;

use App\Models\CharacterCreationEvent;
use App\Models\PetActionExecution;
use App\Models\PetNeedSnapshot;
use App\Models\PetViewSession;
use BackedEnum;
use Carbon\CarbonInterface;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

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
        $needSnapshotsForAnalysis = PetNeedSnapshot::query()
            ->whereBetween('recorded_at', [$from, $to])
            ->orderBy('recorded_at')
            ->orderBy('id')
            ->get();
        $needSnapshots = $needSnapshotsForAnalysis
            ->sortByDesc('recorded_at')
            ->take(50)
            ->sortBy('recorded_at');
        $actionRows = [];

        foreach ($executions as $key => $rows) {
            $finishedRows = $rows->where('status', 'finished');
            $startedRows = $rows->whereNotNull('started_at');
            $activeRows = $rows->whereIn('status', ['requested', 'started']);
            $totalMilliseconds = (int) $finishedRows->sum('duration_milliseconds');
            $actionRows[] = [
                'key' => $key,
                'started' => $startedRows->count(),
                'active' => $activeRows->count(),
                'finished' => $finishedRows->count(),
                'abandoned' => $rows->where('status', 'abandoned')->count(),
                'controller' => $rows->where('source', 'controller')->count(),
                'autonomous' => $rows->where('source', 'autonomous')->count(),
                'averageStartMilliseconds' => (int) round($startedRows->avg(fn (PetActionExecution $execution): int => (int) $execution->requested_at->diffInMilliseconds($execution->started_at)) ?? 0),
                'totalMilliseconds' => $totalMilliseconds,
                'averageMilliseconds' => $finishedRows->isEmpty() ? 0 : (int) round($totalMilliseconds / $finishedRows->count()),
                'averageNeedEffects' => $this->averageNeedEffects($finishedRows),
                'averageNeedsBefore' => $this->averageNeedsBefore($rows),
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
            'criticalNeeds' => $this->criticalNeeds($needSnapshotsForAnalysis, $to),
        ];
    }

    /**
     * @param  Collection<int, PetActionExecution>  $executions
     * @return array{satiety: int, energy: int, happiness: int}
     */
    private function averageNeedEffects(Collection $executions): array
    {
        $executions = $executions->filter(fn (PetActionExecution $execution): bool => $execution->needs_before !== null && $execution->needs_after !== null);

        return $this->averageNeeds($executions, fn (PetActionExecution $execution, string $need): int => $this->needValue($execution->needs_after, $need) - $this->needValue($execution->needs_before, $need));
    }

    /**
     * @param  Collection<int, PetActionExecution>  $executions
     * @return array{satiety: int, energy: int, happiness: int}
     */
    private function averageNeedsBefore(Collection $executions): array
    {
        $executions = $executions->filter(fn (PetActionExecution $execution): bool => $execution->needs_before !== null);

        return $this->averageNeeds($executions, fn (PetActionExecution $execution, string $need): int => $this->needValue($execution->needs_before, $need));
    }

    /**
     * @param  Collection<int, PetActionExecution>  $executions
     * @param  callable(PetActionExecution, string): int  $value
     * @return array{satiety: int, energy: int, happiness: int}
     */
    private function averageNeeds(Collection $executions, callable $value): array
    {
        return [
            'satiety' => $this->averageNeed($executions, 'satiety', $value),
            'energy' => $this->averageNeed($executions, 'energy', $value),
            'happiness' => $this->averageNeed($executions, 'happiness', $value),
        ];
    }

    /**
     * @param  Collection<int, PetActionExecution>  $executions
     * @param  callable(PetActionExecution, string): int  $value
     */
    private function averageNeed(Collection $executions, string $need, callable $value): int
    {
        return (int) round($executions->avg(fn (PetActionExecution $execution): int => $value($execution, $need)) ?? 0);
    }

    /** @param array<string, mixed>|null $needs */
    private function needValue(?array $needs, string $need): int
    {
        return (int) ($needs[$need] ?? 0);
    }

    /**
     * @param  Collection<int, PetNeedSnapshot>  $snapshots
     * @return array<string, array{samples: int, estimatedSeconds: int}>
     */
    private function criticalNeeds(Collection $snapshots, CarbonInterface $to): array
    {
        $snapshots = $snapshots->values();

        return collect(['satiety', 'energy', 'happiness'])
            ->mapWithKeys(function (string $need) use ($snapshots, $to): array {
                $criticalSnapshots = $snapshots->filter(fn (PetNeedSnapshot $snapshot): bool => $snapshot->{$need} <= 20);
                $estimatedSeconds = 0;

                foreach ($snapshots as $index => $snapshot) {
                    if ($snapshot->{$need} > 20 || $snapshot->recorded_at === null) {
                        continue;
                    }

                    $endsAt = $snapshots->has($index + 1)
                        ? $snapshots->get($index + 1)->recorded_at ?? $to
                        : $to;
                    $estimatedSeconds += min(300, max(0, (int) $snapshot->recorded_at->diffInSeconds($endsAt)));
                }

                return [$need => [
                    'samples' => $criticalSnapshots->count(),
                    'estimatedSeconds' => $estimatedSeconds,
                ]];
            })
            ->all();
    }
}
