# Plant corner light

## Goal

Add a second light source over the far corner of the room where the plant stands, and make the plant taller.

## Stages

- [x] Located the Three.js room scene and plant placement.
- [x] Added a warm point light above the plant corner, linked to the shared lighting control.
- [x] Increased only the plant model's vertical scale while preserving its floor footprint.
- [x] Verified the production asset build and the affected feature test.
- [x] Consolidated room-light configuration and removed shadows from the decorative corner light.

## Changed areas

- `resources/js/demo.js`

## Performance review

- The six room assets total about 1.9 MB; the largest one is the frame model at about 600 KB.
- The added point light does not cast shadows, avoiding an additional six-face point-light shadow map on every frame.
- Pixel density is already capped at 1.5, which limits GPU load on high-density displays.

## Verification

- `npm run build` passed.
- `APP_ENV=testing php artisan test --compact tests/Feature/DemoTest.php` passed: 2 tests, 14 assertions.
- The same build and test were rerun after the lighting refactor and passed.
