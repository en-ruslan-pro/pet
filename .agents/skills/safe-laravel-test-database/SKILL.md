---
name: safe-laravel-test-database
description: Safely run Pest or PHPUnit in this Laravel project without allowing RefreshDatabase to touch the local MySQL database. Use before every test command.
---

# Safe Laravel Test Database

Run the preflight before every Pest, PHPUnit, or `artisan test` command in this repository:

```bash
bash .agents/skills/safe-laravel-test-database/scripts/verify-test-database.sh
```

Only after it succeeds, run the narrowest relevant test command. Use `vendor/bin/pest` without manually setting `APP_ENV`; `phpunit.xml` owns the test environment.

## Fail closed

Do not run tests when the preflight fails. Do not work around a failure by changing `DB_CONNECTION`, passing a database URL, clearing tables, or running migrations.

The project uses Pest's `RefreshDatabase` trait for feature tests. If Laravel loads a cached configuration pointing to MySQL, a test run can execute `migrate:fresh` against the local application database and erase its data.

Before retrying after a cache failure, clear only Laravel's configuration cache with:

```bash
php artisan config:clear
```

Then rerun the preflight. The required safe configuration is `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:` in `.env.testing`.

Stop and ask the user if the test configuration cannot be verified, if a test needs a non-SQLite database, or if any command would modify a non-test database.
