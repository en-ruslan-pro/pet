# Settings Livewire test authentication

## Goal

Restore the failing profile and security tests in GitHub Actions.

## Constraints and completion criteria

- Keep the existing profile, password, and account-deletion behavior unchanged.
- Authenticate each Livewire component test through Livewire's test API.
- Preserve valid and invalid password coverage.

## Work stages

1. **Complete:** identified an outdated route cache generated with a different `APP_KEY`, which made Livewire component update requests return 404.
2. **Complete:** cleared the stale route cache and changed settings component tests to use `Livewire::actingAs($user)`.
3. **Complete:** added a successful-response assertion to the profile update test so a failed Livewire request cannot be mistaken for a validation-free response.

## Result and verification

- Changed areas: profile and security feature tests.
- Verification: `APP_ENV=testing vendor/bin/pest --compact` — 49 tests passed.
- Follow-up: do not deploy `bootstrap/cache/routes-v7.php`; the deployment workflow clears Laravel caches after each release.
