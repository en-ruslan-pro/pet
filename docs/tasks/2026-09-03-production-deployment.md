# Production deployment

## Goal

Add a GitHub Actions workflow that deploys the `main` branch to production and checks `https://pet.ruslan.pro`.

## Constraints and completion criteria

- Deployment must use repository secrets for all server access details.
- Only one production deployment may run at a time.
- Deployment must use the exact commit that triggered the workflow.
- The production domain must be checked after deployment.

## Work stages

1. **Complete:** reviewed the existing CI workflow and the requested reference; the reference URL returned 404.
2. **Complete:** added an SSH deployment workflow with dependency installation, asset build, migrations, and Laravel cache optimisation.
3. **Complete:** added a production health check for the configured domain.
4. **Complete:** changed SSH authentication from a private key to the configured deployment password.
5. **Complete:** seed the idempotent pet catalog after database migrations.

## Result and verification

- Changed area: `.github/workflows/deploy.yml`.
- Required repository secrets: `DEPLOY_HOST`, `DEPLOY_PORT`, `DEPLOY_USER`, `DEPLOY_PASSWORD`, and `DEPLOY_PATH`.
- Verification: workflow YAML was parsed locally; production deployment cannot be run until the repository secrets and production server checkout are configured.
- The deploy script runs `php artisan db:seed --class=PetCatalogSeeder --force` after `php artisan migrate --force` so the character catalog is updated with each release.
