<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Мурка — Virtual Pet TV</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        @vite('resources/js/demo.js')

        <style>
            :root {
                color-scheme: dark;
                font-family: Inter, ui-sans-serif, system-ui, sans-serif;
            }

            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                min-height: 100vh;
                overflow: hidden;
                background: #11100f;
            }

            #pet-demo,
            #pet-demo canvas {
                display: block;
                width: 100%;
                height: 100vh;
            }

            .demo-label {
                position: fixed;
                top: clamp(1.25rem, 3vw, 3rem);
                left: clamp(1.25rem, 3vw, 3rem);
                z-index: 1;
                display: grid;
                gap: 0.35rem;
                pointer-events: none;
            }

            .demo-label__eyebrow {
                margin: 0;
                color: rgb(255 255 255 / 58%);
                font-size: 0.7rem;
                font-weight: 700;
                letter-spacing: 0.18em;
                text-transform: uppercase;
            }

            .demo-label__title {
                margin: 0;
                color: #fff9ee;
                font-family: Georgia, serif;
                font-size: clamp(2rem, 4vw, 4.2rem);
                font-weight: 400;
                line-height: 0.95;
            }

            .demo-status {
                position: fixed;
                right: clamp(1.25rem, 3vw, 3rem);
                bottom: clamp(1.25rem, 3vw, 3rem);
                z-index: 1;
                display: flex;
                align-items: center;
                gap: 0.6rem;
                color: rgb(255 255 255 / 74%);
                font-size: 0.75rem;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .demo-status__dot {
                width: 0.55rem;
                height: 0.55rem;
                border-radius: 999px;
                background: #dd9a45;
                box-shadow: 0 0 1rem #dd9a45;
            }

            .demo-error {
                display: grid;
                min-height: 100vh;
                place-items: center;
                padding: 2rem;
                color: #fff9ee;
                text-align: center;
            }

            .demo-credit {
                position: fixed;
                bottom: clamp(1.25rem, 3vw, 3rem);
                left: clamp(1.25rem, 3vw, 3rem);
                z-index: 1;
                color: rgb(255 255 255 / 45%);
                font-size: 0.6875rem;
                text-decoration: none;
            }

            .demo-credit:hover,
            .demo-credit:focus-visible {
                color: #fff9ee;
            }
        </style>
    </head>
    <body>
        <main id="pet-demo" aria-label="Virtual Pet TV: Мурка живёт в своей комнате"></main>

        <div class="demo-label" aria-hidden="true">
            <p class="demo-label__eyebrow">Virtual Pet TV · Demo</p>
            <p class="demo-label__title">Мурка дома</p>
        </div>

        <div class="demo-status" aria-live="polite">
            <span class="demo-status__dot"></span>
            <span id="pet-action">Просыпается</span>
        </div>

        <a class="demo-credit" href="https://sketchfab.com/3d-models/stripe-the-cat-rigged-and-animated-2e3030b71a6d4b219fdc7304f8e58013" target="_blank" rel="noreferrer">
            Stripe the Cat — DreamNoms, CC BY 4.0
        </a>
    </body>
</html>
