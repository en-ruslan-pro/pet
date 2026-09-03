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
                <p class="mt-3 text-stone-300">Откройте TV Mode на телевизоре, введите код комнаты и управляйте питомцем с телефона или компьютера.</p>

                <form class="mt-8 grid gap-5" method="POST" action="{{ route('room.store') }}">
                    @csrf

                    <fieldset class="grid gap-3">
                        <legend class="font-medium text-stone-100">Выберите персонажа</legend>
                        <div class="flex gap-3 overflow-x-auto pb-2">
                            @foreach ($characters as $character)
                                <label class="w-64 shrink-0 cursor-pointer">
                                    <input class="peer sr-only" type="radio" name="character_id" value="{{ $character->id }}" data-default-name="{{ $character->default_name }}" @checked((string) old('character_id', $characters->first()?->id) === (string) $character->id) required>
                                    <span class="grid min-h-28 gap-1 rounded-2xl border border-stone-700 bg-stone-950 p-4 transition peer-checked:border-amber-300 peer-checked:bg-amber-300/10 peer-focus-visible:ring-2 peer-focus-visible:ring-amber-300">
                                        <span class="font-semibold text-white">{{ $character->name }}</span>
                                        <span class="text-sm text-stone-400">{{ $character->petModel->name }}</span>
                                        <span class="text-sm text-amber-200">По умолчанию: {{ $character->default_name }}</span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>
                    @error('character_id')
                        <p class="text-sm text-red-300">{{ $message }}</p>
                    @enderror

                    <label class="grid gap-2" for="pet_name">
                        <span class="font-medium text-stone-100">Имя питомца <span class="font-normal text-stone-400">(необязательно)</span></span>
                        <input id="pet_name" class="rounded-xl border border-stone-700 bg-stone-950 px-4 py-3 text-white outline-none ring-amber-300 transition focus:ring-2" type="text" name="pet_name" value="{{ old('pet_name') }}" maxlength="30" placeholder="Будет использовано имя персонажа" autofocus>
                    </label>
                    @error('pet_name')
                        <p class="text-sm text-red-300">{{ $message }}</p>
                    @enderror

                    <button class="rounded-xl bg-amber-300 px-4 py-3 font-semibold text-stone-950 transition hover:bg-amber-200" type="submit">Создать комнату</button>
                </form>

                <a class="mt-6 block text-center text-sm text-stone-400 underline hover:text-white" href="{{ route('tv.entry') }}">У меня уже есть код комнаты</a>
            </section>
        </main>
        <script>
            document.querySelectorAll('input[name="character_id"]').forEach((character) => {
                character.addEventListener('change', () => {
                    document.getElementById('pet_name').value = character.dataset.defaultName;
                });
            });
        </script>
    </body>
</html>
