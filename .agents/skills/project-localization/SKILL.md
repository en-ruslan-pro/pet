---
name: project-localization
description: Use when adding or changing user-facing text in this Laravel application. Keep every visible string in English and Ukrainian Laravel language files, and select the locale from the browser with English as the fallback.
---

# Project Localization

Apply this skill whenever a change adds or modifies text that a user can see: Blade and Livewire interfaces, Filament labels and actions, validation messages, notifications, emails, API errors, and JavaScript-rendered UI.

## Translation Requirements

- Do not hard-code user-facing text in PHP, Blade, JavaScript, or configuration. Put it in Laravel language files under `lang/en` and `lang/uk`, using stable, semantic keys.
- Add both English and Ukrainian translations in the same change. Keep interpolation placeholders and pluralization behavior identical between locales.
- Use Laravel translation helpers on the server. For browser-rendered text, pass only the needed translated strings or a keyed translation payload from the server; do not duplicate translated literals in JavaScript.
- Keep technical identifiers, stored values, logs, and developer-only diagnostics untranslated unless they are shown to an end user.

## Locale Selection

- Determine the locale from the browser's preferred language. Accept Ukrainian (`uk` and regional variants such as `uk-UA`) as `uk`; use `en` for English and every unsupported or missing value.
- Configure English as Laravel's default and fallback locale. Never expose an unsupported locale to the application.
- Test locale selection for Ukrainian, English, an unsupported browser language, and a missing browser-language header whenever the selection behavior changes.

## Verification

Add or update focused Pest coverage for the affected translated output. Run the narrowest relevant tests with `.env.testing`.
