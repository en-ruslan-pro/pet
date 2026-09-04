<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $room->pet_name }} — контроллер</title>
        @vite(['resources/css/app.css', 'resources/js/room-controller.js'])
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        <main class="mx-auto flex min-h-screen w-full max-w-lg flex-col justify-center px-6 py-12" data-room-controller data-status-url="{{ route('room.status', $room) }}" data-meow-url="{{ route('room.meow', $room) }}" data-actions-url="{{ route('room.actions', [$room, '__action__']) }}">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-300">Комната {{ $room->code }}</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">{{ $room->pet_name }}</h1>
            <p class="mt-3 text-stone-300">Откройте TV Mode на телевизоре и введите код комнаты.</p>

            <section class="mt-8 grid gap-6 rounded-3xl border border-amber-200/15 bg-stone-900 p-6 shadow-2xl shadow-black/30">
                <p class="text-center font-mono text-3xl font-bold tracking-[0.3em] text-amber-300">{{ $room->code }}</p>

                <a class="rounded-xl border border-amber-300 px-4 py-3 text-center font-semibold text-amber-300 transition hover:bg-amber-300 hover:text-stone-950" href="{{ route('tv.show', $room) }}">Открыть комнату</a>

                <div class="flex items-center justify-between rounded-xl bg-stone-950 px-4 py-3">
                    <span class="text-sm text-stone-300">Телевизор</span>
                    <span class="flex items-center gap-2 text-sm font-medium" data-tv-status><span class="h-2.5 w-2.5 rounded-full bg-stone-500"></span>Проверяем связь</span>
                </div>

                <section class="grid gap-3" aria-label="Состояние питомца">
                    @foreach (['satiety' => __('pet.needs.satiety'), 'energy' => 'Энергия', 'happiness' => 'Настроение'] as $need => $label)
                        <div>
                            <div class="mb-1 flex justify-between text-sm text-stone-300"><span>{{ $label }}</span><span data-need-value="{{ $need }}">{{ $room->petNeeds()[$need] }}</span></div>
                            <div class="h-2 overflow-hidden rounded-full bg-stone-950"><div class="h-full rounded-full bg-emerald-400" data-need-bar="{{ $need }}" style="width: {{ $room->petNeeds()[$need] }}%"></div></div>
                        </div>
                    @endforeach
                </section>

                <button class="rounded-xl bg-amber-300 px-4 py-4 text-lg font-semibold text-stone-950 transition hover:bg-amber-200 disabled:cursor-wait disabled:opacity-60" type="button" data-meow-button>Позвать {{ $room->pet_name }}</button>
                <div class="grid grid-cols-3 gap-3">
                    <button class="rounded-xl bg-stone-800 px-3 py-3 text-sm font-semibold transition hover:bg-stone-700 disabled:cursor-wait disabled:opacity-60" type="button" data-pet-action="feed">Покормить</button>
                    <button class="rounded-xl bg-stone-800 px-3 py-3 text-sm font-semibold transition hover:bg-stone-700 disabled:cursor-wait disabled:opacity-60" type="button" data-pet-action="play">Играть</button>
                    <button class="rounded-xl bg-stone-800 px-3 py-3 text-sm font-semibold transition hover:bg-stone-700 disabled:cursor-wait disabled:opacity-60" type="button" data-pet-action="sleep">Спать</button>
                </div>
                <p class="min-h-6 text-center text-sm text-stone-400" data-command-status aria-live="polite"></p>
            </section>
        </main>
    </body>
</html>
