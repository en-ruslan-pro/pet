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

            .demo-controls {
                position: fixed;
                top: clamp(1.25rem, 3vw, 3rem);
                right: clamp(1.25rem, 3vw, 3rem);
                z-index: 1;
                display: grid;
                gap: 1rem;
                width: min(18rem, calc(100vw - 2.5rem));
                padding: 1rem;
                border: 1px solid rgb(255 255 255 / 16%);
                border-radius: 0.75rem;
                background: rgb(24 20 16 / 74%);
                backdrop-filter: blur(0.75rem);
            }

            .demo-controls[hidden] {
                display: none;
            }

            .demo-controls__section {
                display: grid;
                gap: 0.55rem;
            }

            .demo-controls__title,
            .demo-controls__value {
                margin: 0;
                color: rgb(255 255 255 / 82%);
                font-size: 0.72rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .demo-controls__value {
                color: #f6c477;
                font-variant-numeric: tabular-nums;
                letter-spacing: 0.04em;
                text-transform: none;
            }

            .demo-controls input {
                width: 100%;
                accent-color: #dd9a45;
            }

            .demo-controls select {
                width: 100%;
                min-height: 2rem;
                border: 1px solid rgb(255 255 255 / 16%);
                border-radius: 0.4rem;
                padding: 0 0.55rem;
                background: rgb(255 255 255 / 8%);
                color: #fff9ee;
                font: inherit;
                font-size: 0.78rem;
            }

            .demo-controls select:disabled {
                color: rgb(255 255 255 / 42%);
            }

            .demo-controls__buttons {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.4rem;
            }

            .demo-controls button {
                min-height: 2rem;
                border: 1px solid rgb(255 255 255 / 16%);
                border-radius: 0.4rem;
                background: rgb(255 255 255 / 8%);
                color: #fff9ee;
                cursor: pointer;
                font: inherit;
                font-size: 0.72rem;
            }

            .demo-controls button:hover,
            .demo-controls button:focus-visible {
                border-color: #dd9a45;
                background: rgb(221 154 69 / 18%);
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

        @if (request()->boolean('tv'))
            <aside class="demo-controls" aria-label="Настройка сцены" hidden>
        @else
            <aside class="demo-controls" aria-label="Настройка сцены">
        @endif
            <section class="demo-controls__section">
                <p class="demo-controls__title">Освещение</p>
                <input id="demo-lighting" type="range" min="0.60" max="1.60" step="0.05" value="1.50" aria-label="Интенсивность освещения">
                <output id="demo-lighting-value" class="demo-controls__value">1.50×</output>
            </section>

            <section class="demo-controls__section">
                <label for="demo-animation" class="demo-controls__title">Анимация</label>
                <select id="demo-animation" disabled>
                    <option value="">Авто</option>
                </select>
            </section>

            <section class="demo-controls__section">
                <p class="demo-controls__title">Положение камеры</p>
                <output id="demo-camera-position" class="demo-controls__value">X 7.25 · Y 3.80 · Z 9.75</output>
                <div class="demo-controls__buttons">
                    <button type="button" data-camera-axis="x" data-camera-direction="-1">X −</button>
                    <button type="button" data-camera-axis="y" data-camera-direction="1">Y +</button>
                    <button type="button" data-camera-axis="x" data-camera-direction="1">X +</button>
                    <button type="button" data-camera-axis="z" data-camera-direction="-1">Z −</button>
                    <button type="button" data-camera-axis="y" data-camera-direction="-1">Y −</button>
                    <button type="button" data-camera-axis="z" data-camera-direction="1">Z +</button>
                </div>
            </section>

            <section class="demo-controls__section">
                <p class="demo-controls__title">Верхний свет</p>
                <output id="demo-light-position" class="demo-controls__value">X 0.00 · Y 7.00 · Z 0.00</output>
                <div class="demo-controls__buttons">
                    <button type="button" data-light-axis="x" data-light-direction="-1">X −</button>
                    <button type="button" data-light-axis="y" data-light-direction="1">Y +</button>
                    <button type="button" data-light-axis="x" data-light-direction="1">X +</button>
                    <button type="button" data-light-axis="z" data-light-direction="-1">Z −</button>
                    <button type="button" data-light-axis="y" data-light-direction="-1">Y −</button>
                    <button type="button" data-light-axis="z" data-light-direction="1">Z +</button>
                </div>
            </section>

            <section class="demo-controls__section">
                <label for="demo-light-distance" class="demo-controls__title">Дальность верхнего света</label>
                <input id="demo-light-distance" type="range" min="5" max="30" step="0.5" value="15" aria-label="Дальность верхнего света">
                <output id="demo-light-distance-value" class="demo-controls__value">15.0</output>
            </section>

            <section class="demo-controls__section">
                <p class="demo-controls__title">Свет от камеры</p>
                <p class="demo-controls__value">Следует за камерой</p>
                <label for="demo-camera-light-distance" class="demo-controls__title">Дальность</label>
                <input id="demo-camera-light-distance" type="range" min="5" max="30" step="0.5" value="18" aria-label="Дальность света от камеры">
                <output id="demo-camera-light-distance-value" class="demo-controls__value">18.0</output>
                <label for="demo-camera-light-strength" class="demo-controls__title">Сила</label>
                <input id="demo-camera-light-strength" type="range" min="0" max="2" step="0.05" value="0.90" aria-label="Сила света от камеры">
                <output id="demo-camera-light-strength-value" class="demo-controls__value">0.90</output>
            </section>
        </aside>

        <a class="demo-credit" href="https://sketchfab.com/3d-models/stripe-the-cat-rigged-and-animated-2e3030b71a6d4b219fdc7304f8e58013" target="_blank" rel="noreferrer">
            Stripe the Cat — DreamNoms, CC BY 4.0
        </a>
    </body>
</html>
