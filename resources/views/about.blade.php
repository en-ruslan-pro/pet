<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>О проекте — Virtual Pet TV</title>

        <style>
            :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
            * { box-sizing: border-box; }
            body { min-width: 20rem; margin: 0; background: #171411; color: #fff9ee; }
            main { width: min(42rem, calc(100% - 3rem)); margin: 0 auto; padding: clamp(3rem, 12vw, 8rem) 0; }
            a { color: #f6c477; }
            .back { color: rgb(255 249 238 / 68%); font-size: .9rem; text-decoration: none; }
            .back:hover, .back:focus-visible { color: #f6c477; }
            .eyebrow { margin: 3rem 0 .75rem; color: #f6c477; font-size: .8rem; font-weight: 800; letter-spacing: .18em; text-transform: uppercase; }
            h1 { margin: 0; font-family: Georgia, serif; font-size: clamp(2.8rem, 8vw, 5rem); font-weight: 400; line-height: .95; }
            p { color: rgb(255 249 238 / 74%); font-size: 1.05rem; line-height: 1.65; }
            .credit { margin-top: 2.5rem; padding: 1.5rem; border: 1px solid rgb(255 255 255 / 13%); border-radius: 1rem; background: rgb(45 36 29 / 66%); }
            .credit h2 { margin: 0; color: #fff9ee; font-size: 1rem; }
            .credit p { margin-bottom: 0; font-size: .95rem; }
        </style>
    </head>
    <body>
        <main>
            <a class="back" href="{{ route('home') }}">← На главную</a>
            <p class="eyebrow">Virtual Pet TV</p>
            <h1>О проекте</h1>
            <p>Virtual Pet TV — виртуальный питомец, который живёт на большом экране, пока вы заботитесь о нём с телефона или компьютера.</p>

            <section class="credit" aria-labelledby="model-credit">
                <h2 id="model-credit">Лицензия модели питомца</h2>
                <p><a href="https://sketchfab.com/3d-models/stripe-the-cat-rigged-and-animated-2e3030b71a6d4b219fdc7304f8e58013" target="_blank" rel="noreferrer">Stripe the Cat</a> — DreamNoms, лицензия CC BY 4.0.</p>
            </section>
        </main>
    </body>
</html>
