# Filament Shield administrator access

## Goal

Configure Filament Shield so only users with the `admin` role can access the `dostup` Filament panel, including in production.

## Constraints and completion criteria

- Use Shield and Spatie Laravel Permission with the existing `User` model.
- Do not create a default administrator account or hardcode credentials.
- Make access explicit for the `dostup` panel and cover both allowed and rejected requests.

## Work stages

1. **Complete:** published the Spatie Permission configuration and migration required for roles.
2. **Complete:** connected Shield to the Filament panel and configured `admin` as its super-admin role.
3. **Complete:** added production-safe panel authorization, an idempotent role seeder, and authorization tests.

## Result and verification

- Changed areas: Filament panel, user authorization, Shield/Permission configuration, migration, seeder, and Feature tests.
- Verification: `APP_ENV=testing vendor/bin/pest --compact tests/Feature/Filament/DostupPanelAccessTest.php` — 2 tests passed; `vendor/bin/pint --dirty --format agent` completed; `vendor/bin/phpstan analyse app/Models/User.php app/Providers/Filament/DostupPanelProvider.php database/seeders/AdminRoleSeeder.php --no-progress` — no errors.
- Production follow-up: deploy the published migration, seed `AdminRoleSeeder`, then deliberately assign the `admin` role to the chosen existing user. No administrator credentials are committed or generated automatically.
