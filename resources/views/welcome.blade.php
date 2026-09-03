<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Virtual Pet TV</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        <style>
            :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
            * { box-sizing: border-box; }
            body { min-width: 20rem; margin: 0; background: #171411; color: #fff9ee; }
            .page { position: relative; min-height: 100vh; overflow: hidden; }
            .glow { position: absolute; width: 42rem; height: 42rem; border-radius: 50%; filter: blur(1rem); opacity: .48; pointer-events: none; }
            .glow--one { top: -24rem; left: -14rem; background: #824f2a; }
            .glow--two { right: -20rem; bottom: -22rem; background: #573821; }
            .content { position: relative; width: min(70rem, calc(100% - 3rem)); margin: 0 auto; padding: 2rem 0 4rem; }
            .header { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
            .brand { color: #f6c477; font-size: .8rem; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
            .about-link { color: rgb(255 249 238 / 68%); font-size: .9rem; text-decoration: none; }
            .about-link:hover, .about-link:focus-visible { color: #f6c477; }
            .hero { display: grid; grid-template-columns: minmax(0, 1.25fr) minmax(16rem, .75fr); gap: clamp(2.5rem, 8vw, 7rem); align-items: center; min-height: calc(100vh - 7rem); }
            h1 { max-width: 12ch; margin: 1rem 0; font-family: Georgia, serif; font-size: clamp(3.2rem, 8vw, 6.5rem); font-weight: 400; line-height: .92; letter-spacing: -.05em; }
            .lead { max-width: 38rem; margin: 0; color: rgb(255 249 238 / 74%); font-size: clamp(1.05rem, 2vw, 1.25rem); line-height: 1.6; }
            .cta { display: inline-flex; align-items: center; gap: .75rem; margin-top: 2rem; padding: 1rem 1.25rem; border-radius: .8rem; background: #f6c477; color: #28170b; font-weight: 800; text-decoration: none; transition: transform .2s ease, background .2s ease; }
            .cta:hover, .cta:focus-visible { background: #ffe0a6; transform: translateY(-2px); }
            .room-entry { display: grid; grid-template-columns: 1fr auto; gap: .65rem; max-width: 31rem; margin-top: 1.25rem; }
            .room-entry__label { grid-column: 1 / -1; color: rgb(255 249 238 / 68%); font-size: .85rem; font-weight: 700; }
            .room-entry__input { min-width: 0; border: 1px solid rgb(255 255 255 / 18%); border-radius: .8rem; padding: .9rem 1rem; background: rgb(24 20 16 / 72%); color: #fff9ee; font: 700 1rem/1 ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing: .18em; outline: none; text-align: center; text-transform: uppercase; }
            .room-entry__input:focus { border-color: #f6c477; box-shadow: 0 0 0 3px rgb(246 196 119 / 22%); }
            .room-entry__submit { border: 0; border-radius: .8rem; padding: .9rem 1rem; background: rgb(255 249 238 / 12%); color: #fff9ee; cursor: pointer; font: 800 .9rem/1 Inter, ui-sans-serif, system-ui, sans-serif; transition: background .2s ease, transform .2s ease; }
            .room-entry__submit:hover, .room-entry__submit:focus-visible { background: rgb(246 196 119 / 24%); transform: translateY(-2px); }
            .steps { display: grid; gap: 1rem; padding: 1.5rem; border: 1px solid rgb(255 255 255 / 13%); border-radius: 1.5rem; background: rgb(45 36 29 / 66%); backdrop-filter: blur(1rem); }
            .steps__title { margin: 0 0 .4rem; color: #f6c477; font-size: .75rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
            .step { display: grid; grid-template-columns: 2rem 1fr; gap: .8rem; align-items: start; }
            .step__number { display: grid; width: 2rem; height: 2rem; place-items: center; border-radius: 50%; background: rgb(246 196 119 / 16%); color: #f6c477; font-size: .8rem; font-weight: 800; }
            .step p { margin: .25rem 0 0; color: rgb(255 249 238 / 68%); font-size: .92rem; line-height: 1.45; }
            .step strong { font-size: .95rem; }
            @media (max-width: 44rem) { .content { width: min(100% - 2.5rem, 34rem); } .hero { grid-template-columns: 1fr; align-content: center; padding: 4rem 0; } h1 { max-width: 10ch; } }
        </style>
    </head>
    <body>
        <main class="page">
            <div class="glow glow--one"></div>
            <div class="glow glow--two"></div>

            <div class="content">
                <header class="header">
                    <span class="brand">Virtual Pet TV</span>
                    <a class="about-link" href="{{ route('about') }}">О проекте</a>
                </header>

                <section class="hero">
                    <div>
                        <h1>Ваш питомец живёт на телевизоре.</h1>
                        <p class="lead">Создайте уютную комнату, наблюдайте за питомцем на большом экране и управляйте им с телефона или компьютера.</p>
                        <a class="cta" href="{{ route('room.create') }}">Создать питомца <span aria-hidden="true">→</span></a>

                        <form class="room-entry" method="POST" action="{{ route('tv.enter') }}">
                            @csrf
                            <label class="room-entry__label" for="room-code">Уже есть комната? Откройте её на телевизоре</label>
                            <input id="room-code" class="room-entry__input" type="text" name="code" value="{{ old('code') }}" inputmode="text" maxlength="6" autocomplete="one-time-code" placeholder="КОД" required>
                            <button class="room-entry__submit" type="submit">Открыть TV</button>
                        </form>
                    </div>

                    <section class="steps" aria-label="Как это работает">
                        <p class="steps__title">Как это работает</p>
                        <div class="step"><span class="step__number">1</span><div><strong>Создайте питомца</strong><p>Дайте ему имя — мы подготовим комнату и код подключения.</p></div></div>
                        <div class="step"><span class="step__number">2</span><div><strong>Откройте TV Mode</strong><p>Введите код комнаты на телевизоре и наблюдайте за жизнью питомца.</p></div></div>
                        <div class="step"><span class="step__number">3</span><div><strong>Позовите питомца</strong><p>Используйте контроллер на телефоне, чтобы отправлять ему команды.</p></div></div>
                    </section>
                </section>
            </div>
        </main>
    </body>
</html>
