#!/usr/bin/env bash
set -euo pipefail

COMPOSER_BIN="$(command -v composer)"
if [[ -z "$COMPOSER_BIN" ]]; then
  echo "ERROR: composer not found in PATH" >&2
  exit 1
fi

PHP_BIN="${PHP_BIN:-}"
if [[ -z "$PHP_BIN" ]]; then
  if [[ -x /opt/homebrew/bin/php ]]; then
    PHP_BIN="/opt/homebrew/bin/php"
  else
    PHP_BIN="$(command -v php)"
  fi
fi

exec "$PHP_BIN" \
  -d error_reporting='E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED' \
  "$COMPOSER_BIN" "$@"
