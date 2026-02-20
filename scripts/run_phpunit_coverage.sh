#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"

PHP_BIN="${PHP_BIN:-}"
if [[ -z "$PHP_BIN" ]]; then
  if [[ -x /opt/homebrew/bin/php ]]; then
    PHP_BIN="/opt/homebrew/bin/php"
  else
    PHP_BIN="$(command -v php)"
  fi
fi

mkdir -p .artifacts/coverage

if "$PHP_BIN" -r 'exit(extension_loaded("xdebug") ? 0 : 1);'; then
  XDEBUG_MODE=coverage "$PHP_BIN" ./vendor/bin/phpunit \
    --configuration phpunit.xml \
    --coverage-clover .artifacts/coverage/clover.xml \
    --coverage-text \
    --colors=never
  exit 0
fi

if "$PHP_BIN" -r 'exit(extension_loaded("pcov") ? 0 : 1);'; then
  "$PHP_BIN" -d pcov.enabled=1 ./vendor/bin/phpunit \
    --configuration phpunit.xml \
    --coverage-clover .artifacts/coverage/clover.xml \
    --coverage-text \
    --colors=never
  exit 0
fi

cat >&2 <<'EOF'
ERROR: No PHP code coverage driver is available.
Install one of:
  - xdebug (recommended), then run with XDEBUG_MODE=coverage
  - pcov
Examples:
  macOS (Homebrew): pecl install xdebug
  Ubuntu/Debian: sudo apt-get install -y php-xdebug
Then re-run: make test-coverage
EOF
exit 2
