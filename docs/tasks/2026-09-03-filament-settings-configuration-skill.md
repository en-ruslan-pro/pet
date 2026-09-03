# Filament settings configuration skill

## Goal

Create a reusable project skill for deciding when fixed values belong in persistent application settings and for organizing them in Filament.

## Constraints and completion criteria

- Use `spatie/laravel-settings` with the installed Filament plugin.
- Keep administrator-configurable values grouped by business domain and form section.
- Preserve true technical constants and secrets outside administrator settings.

## Work stages

1. **Complete:** confirmed Filament 5 and `filament/spatie-laravel-settings-plugin` 5 are installed, with `spatie/laravel-settings` 3.9.
2. **Complete:** added the reusable project skill with classification, organization, implementation, and verification guidance.

## Result and verification

- Changed areas: `.agents/skills/filament-settings-configuration`.
- Verification: manually reviewed the required YAML frontmatter and ran `git diff --check` successfully. The bundled `quick_validate.py` could not run because the host Python lacks the `yaml` module; no project dependency was added for this documentation-only skill.
