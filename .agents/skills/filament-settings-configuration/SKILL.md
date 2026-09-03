---
name: filament-settings-configuration
description: Use when adding or changing business-configurable values in this Laravel application. Store appropriate values with spatie/laravel-settings and expose them through Filament settings pages grouped by domain and section.
---

# Filament Settings Configuration

Use this skill when a value currently embedded in application code is a reasonable product or operational setting: a threshold, duration, limit, toggle, tuning value, default, label, or policy that an administrator may need to change without a deployment.

This application uses `spatie/laravel-settings` with `filament/spatie-laravel-settings-plugin` 5.x. Treat those values as persistent application settings, not as Laravel `config()` values.

## Decide Whether a Value Is Configurable

Move a fixed value into settings when changing it is a legitimate administrative decision and its new value can be safely validated. Typical examples include gameplay timings, pet-care thresholds, feature switches, display defaults, notification rules, and domain limits.

Keep a value in code when it is a protocol or framework requirement, a security-sensitive secret, a database/schema invariant, a derived value, or an implementation detail with no meaningful administrator choice. Keep deployment-specific infrastructure values and secrets in environment-backed Laravel configuration; never place secrets in Spatie settings or expose them in Filament.

Do not make values configurable merely because they are literals. Prefer a small, intentional setting surface over a control panel for every implementation detail.

## Organize Settings by Meaning

Before adding a setting, inspect existing setting classes and Filament settings pages. Extend an existing domain when the audience and purpose match; otherwise add a focused domain-specific settings class and page.

- One settings class owns one coherent domain, such as `PetCareSettings`, `GameBalanceSettings`, or `NotificationSettings`.
- Give each settings class its own settings group and persistence key according to the package conventions used in the project.
- Put each settings class on a separate Filament settings page (or the established equivalent) when it represents a distinct administrative topic. Use clear navigation grouping and labels.
- Within a page, use schema sections for related fields. A section should answer one administrator question, such as “Hunger”, “Rest”, or “Reminders”. Do not collect unrelated settings on a generic “General” page.
- Keep dependent controls adjacent, use contextual helper text and units, and make destructive or advanced options visually distinct when appropriate.

## Implementation Rules

Declare safe defaults in the settings class so a fresh installation behaves predictably. Use explicit property types and meaningful names; keep domain calculations dependent on injected/resolved settings rather than duplicating fallback literals.

Validate every editable setting at the Filament form boundary. Use suitable controls and constraints for its type: numeric bounds and units for thresholds/durations, toggles for booleans, and constrained selections for finite choices. Prevent invalid combinations when fields are interdependent.

When replacing a literal, update every relevant code path to use the setting and remove duplicate values. Preserve existing behavior by selecting the former literal as the default, unless the requested product behavior says otherwise. Add or update Pest coverage for the setting-dependent behavior and meaningful validation failures.

Before using a version-sensitive package API, verify its syntax against the installed package or Laravel documentation. Follow the project’s existing migrations, settings discovery, authorization, and Filament conventions rather than introducing parallel registration mechanisms.

## Verification

Run the narrowest affected Pest tests with `.env.testing`, then format changed PHP files with `vendor/bin/pint --dirty --format agent`. Confirm that a fresh/default settings state retains the former behavior and that the form presents the values in the intended domain and section.
