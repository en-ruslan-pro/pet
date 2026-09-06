<x-filament-panels::page>
    <div class="grid gap-6">
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('pet.analytics.period', ['from' => $from->toDateString(), 'to' => $to->toDateString()]) }}
        </p>

        <div class="grid gap-4 md:grid-cols-3">
            <x-filament::section>
                <x-slot name="heading">{{ __('pet.analytics.created') }}</x-slot>
                <p class="text-3xl font-semibold">{{ $createdCount }}</p>
            </x-filament::section>
            <x-filament::section>
                <x-slot name="heading">{{ __('pet.analytics.view_sessions') }}</x-slot>
                <p class="text-3xl font-semibold">{{ $viewSessionCount }}</p>
                <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('pet.analytics.view_duration', ['seconds' => $viewDurationSeconds]) }}</p>
            </x-filament::section>
            <x-filament::section>
                <x-slot name="heading">{{ __('pet.analytics.need_samples') }}</x-slot>
                <p class="text-3xl font-semibold">{{ $needSnapshots->count() }}</p>
            </x-filament::section>
        </div>

        <x-filament::section :heading="__('pet.analytics.creations_by_character')">
            <div class="grid gap-2 md:grid-cols-3">
                @forelse ($creationsByCharacter as $character => $events)
                    <div class="rounded-lg bg-gray-50 p-3 dark:bg-white/5">
                        <p class="font-medium">{{ $character }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('pet.analytics.created_count', ['count' => $events->count()]) }}</p>
                    </div>
                @empty
                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ __('pet.analytics.no_data') }}</p>
                @endforelse
            </div>
        </x-filament::section>

        <x-filament::section :heading="__('pet.analytics.actions')">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-gray-600 dark:text-gray-400">
                        <tr>
                            <th class="px-2 py-2">{{ __('pet.analytics.action') }}</th>
                            <th class="px-2 py-2">{{ __('pet.analytics.started') }}</th>
                            <th class="px-2 py-2">{{ __('pet.analytics.active') }}</th>
                            <th class="px-2 py-2">{{ __('pet.analytics.finished') }}</th>
                            <th class="px-2 py-2">{{ __('pet.analytics.abandoned') }}</th>
                            <th class="px-2 py-2">{{ __('pet.analytics.total_duration') }}</th>
                            <th class="px-2 py-2">{{ __('pet.analytics.average_duration') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($actionRows as $row)
                            <tr class="border-b border-gray-100 dark:border-white/10">
                                <td class="px-2 py-2">{{ __('pet.actions.'.$row['key']) }}</td>
                                <td class="px-2 py-2">{{ $row['started'] }}</td>
                                <td class="px-2 py-2">{{ $row['active'] }}</td>
                                <td class="px-2 py-2">{{ $row['finished'] }}</td>
                                <td class="px-2 py-2">{{ $row['abandoned'] }}</td>
                                <td class="px-2 py-2">{{ __('pet.analytics.seconds', ['seconds' => round($row['totalMilliseconds'] / 1000, 1)]) }}</td>
                                <td class="px-2 py-2">{{ __('pet.analytics.seconds', ['seconds' => round($row['averageMilliseconds'] / 1000, 1)]) }}</td>
                            </tr>
                        @empty
                            <tr><td class="px-2 py-3" colspan="7">{{ __('pet.analytics.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section :heading="__('pet.analytics.history_limit', ['count' => $needSnapshotLimit])">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b text-gray-600 dark:text-gray-400">
                        <tr><th class="px-2 py-2">{{ __('pet.analytics.recorded_at') }}</th><th class="px-2 py-2">{{ __('pet.analytics.reason') }}</th><th class="px-2 py-2">{{ __('pet.needs.satiety') }}</th><th class="px-2 py-2">{{ __('pet.needs.energy') }}</th><th class="px-2 py-2">{{ __('pet.needs.happiness') }}</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($needSnapshots as $snapshot)
                            <tr class="border-b border-gray-100 dark:border-white/10"><td class="px-2 py-2">{{ $snapshot->recorded_at?->format('Y-m-d H:i:s') }}</td><td class="px-2 py-2">{{ __('pet.analytics.snapshot_reasons.'.$snapshot->reason) }}</td><td class="px-2 py-2">{{ $snapshot->satiety }}</td><td class="px-2 py-2">{{ $snapshot->energy }}</td><td class="px-2 py-2">{{ $snapshot->happiness }}</td></tr>
                        @empty
                            <tr><td class="px-2 py-3" colspan="5">{{ __('pet.analytics.no_data') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
