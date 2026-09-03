#!/usr/bin/env bash

set -euo pipefail

if [[ -f bootstrap/cache/config.php ]]; then
    echo 'Refusing to run tests: Laravel configuration cache exists.' >&2
    echo 'Run php artisan config:clear, then rerun this preflight.' >&2
    exit 1
fi

if [[ ! -f .env.testing ]]; then
    echo 'Refusing to run tests: .env.testing is missing.' >&2
    exit 1
fi

if ! rg --quiet '^DB_CONNECTION=sqlite$' .env.testing; then
    echo 'Refusing to run tests: .env.testing must use DB_CONNECTION=sqlite.' >&2
    exit 1
fi

if ! rg --quiet '^DB_DATABASE=:memory:$' .env.testing; then
    echo 'Refusing to run tests: .env.testing must use DB_DATABASE=:memory:.' >&2
    exit 1
fi

echo 'Test database preflight passed: SQLite in-memory database is required.'
