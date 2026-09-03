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
        <main class="mx-auto flex min-h-screen w-full max-w-lg flex-col justify-center px-6 py-12" data-room-controller data-status-url="{{ route('room.status', $room) }}" data-meow-url="{{ route('room.meow', $room) }}">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-300">Комната {{ $room->code }}</p>
            <h1 class="mt-3 text-4xl font-semibold text-white">{{ $room->pet_name }}</h1>
            <p class="mt-3 text-stone-300">Откройте TV Mode и введите код комнаты или покажите QR-код телевизору.</p>

            <section class="mt-8 grid gap-6 rounded-3xl border border-amber-200/15 bg-stone-900 p-6 shadow-2xl shadow-black/30">
                <canvas class="mx-auto rounded-xl bg-white p-3" data-room-qr data-controller-url="{{ route('room.show', $room) }}" aria-label="QR-код для открытия контроллера"></canvas>
                <p class="text-center font-mono text-3xl font-bold tracking-[0.3em] text-amber-300">{{ $room->code }}</p>

                <div class="flex items-center justify-between rounded-xl bg-stone-950 px-4 py-3">
                    <span class="text-sm text-stone-300">Телевизор</span>
                    <span class="flex items-center gap-2 text-sm font-medium" data-tv-status><span class="h-2.5 w-2.5 rounded-full bg-stone-500"></span>Проверяем связь</span>
                </div>

                <button class="rounded-xl bg-amber-300 px-4 py-4 text-lg font-semibold text-stone-950 transition hover:bg-amber-200 disabled:cursor-wait disabled:opacity-60" type="button" data-meow-button>Позвать {{ $room->pet_name }}</button>
                <p class="min-h-6 text-center text-sm text-stone-400" data-command-status aria-live="polite"></p>
            </section>
        </main>
    </body>
</html>
