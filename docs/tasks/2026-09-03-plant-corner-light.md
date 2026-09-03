# Plant corner light

## Goal

Add a second light source over the far corner of the room where the plant stands, and make the plant taller. Follow-up: visibly brighten this corner and expose a dedicated strength control in the demo.

## Stages

- [x] Located the Three.js room scene and plant placement.
- [x] Added a warm point light above the plant corner, linked to the shared lighting control.
- [x] Increased only the plant model's vertical scale while preserving its floor footprint.
- [x] Verified the production asset build and the affected feature test.
- [x] Consolidated room-light configuration and removed shadows from the decorative corner light.
- [x] Increased the plant-corner light's default strength from 58 to 110 and added a 0–200 dedicated demo slider.
- [x] Verified the demo UI, feature test, and production asset build.
- [x] Grouped demo light controls by source; top, plant, and camera lights now expose independent strength and range settings. The hemisphere light exposes strength only because it has no attenuation range.
- [x] Verified the revised controls in the browser, feature test, and production build.
- [x] Kept the expanded demo-controls panel inside the viewport with a visible vertical scrollbar.
- [x] Verified the panel scrolls to its lower source settings.

## Changed areas

- `resources/js/demo.js`
- `resources/views/demo.blade.php`
- `tests/Feature/DemoTest.php`

## Performance review

- The six room assets total about 1.9 MB; the largest one is the frame model at about 600 KB.
- The added point light does not cast shadows, avoiding an additional six-face point-light shadow map on every frame.
- Pixel density is already capped at 1.5, which limits GPU load on high-density displays.

## Verification

- `npm run build` passed.
- `APP_ENV=testing php artisan test --compact tests/Feature/DemoTest.php` passed: 2 tests, 14 assertions.
- The same build and test were rerun after the lighting refactor and passed.
- `APP_ENV=testing php artisan test --compact tests/Feature/DemoTest.php` passed: 4 tests, 20 assertions.
- `npm run build` passed. The bundle-size advisory remains for `demo`; no build errors occurred.
- Browser check at `/demo`: the control starts at 110, updates the displayed value through its 0–200 range, and emits no console errors.
- `APP_ENV=testing php artisan test --compact tests/Feature/DemoTest.php` passed: 4 tests, 23 assertions.
- `npm run build` passed. Browser check confirmed the grouped controls and each output update, with no console errors.
- `APP_ENV=testing php artisan test --compact tests/Feature/DemoTest.php` passed: 4 tests, 25 assertions.
- `npm run build` passed. Browser check confirmed the panel's scrollable content reaches the lower «Рассеянный свет» controls.
