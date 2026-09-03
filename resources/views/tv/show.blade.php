<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ $room->pet_name }} — Virtual Pet TV</title>
        @vite('resources/js/tv-room.js')
        <style>
            html, body, main, iframe { width: 100%; height: 100%; margin: 0; border: 0; background: #11100f; }
            .tv-room__status { position: fixed; right: 2rem; top: 2rem; z-index: 1; color: #fff9ee; font: 600 .75rem/1 ui-sans-serif, system-ui; letter-spacing: .12em; text-transform: uppercase; opacity: .72; }
        </style>
    </head>
    <body>
        <main data-tv-room data-room-code="{{ $room->code }}" data-heartbeat-url="{{ route('tv.heartbeat', $room) }}" data-reverb='@json($reverb)'>
            <iframe title="{{ $room->pet_name }} дома" src="{{ route('demo') }}"></iframe>
            <p class="tv-room__status" data-tv-room-status>Подключаем {{ $room->pet_name }}</p>
        </main>
    </body>
</html>
