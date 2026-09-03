<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Создать комнату — Virtual Pet TV</title>
        @vite('resources/css/app.css')
    </head>
    <body class="min-h-screen bg-stone-950 text-stone-100">
        <main class="mx-auto grid min-h-screen w-full max-w-lg place-items-center px-6 py-12">
            <section class="w-full rounded-3xl border border-amber-200/15 bg-stone-900 p-8 shadow-2xl shadow-black/30">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-amber-300">Virtual Pet TV</p>
                <h1 class="mt-3 text-3xl font-semibold text-white">Создайте комнату для питомца</h1>
                <p class="mt-3 text-stone-300">Откройте комнату на телевизоре, отсканируйте QR-код телефоном и позовите питомца.</p>

                <form class="mt-8 grid gap-5" method="POST" action="{{ route('room.store') }}">
                    @csrf

                    <label class="grid gap-2" for="pet_name">
                        <span class="font-medium text-stone-100">Имя питомца</span>
                        <input id="pet_name" class="rounded-xl border border-stone-700 bg-stone-950 px-4 py-3 text-white outline-none ring-amber-300 transition focus:ring-2" type="text" name="pet_name" value="{{ old('pet_name', 'Мурка') }}" maxlength="30" required autofocus>
                    </label>
                    @error('pet_name')
                        <p class="text-sm text-red-300">{{ $message }}</p>
                    @enderror

                    <button class="rounded-xl bg-amber-300 px-4 py-3 font-semibold text-stone-950 transition hover:bg-amber-200" type="submit">Создать комнату</button>
                </form>

                <a class="mt-6 block text-center text-sm text-stone-400 underline hover:text-white" href="{{ route('tv.entry') }}">У меня уже есть код комнаты</a>
            </section>
        </main>
    </body>
</html>
