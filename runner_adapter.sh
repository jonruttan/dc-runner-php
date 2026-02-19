#!/usr/bin/env bash
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
subcommand="${1:-}"
shift || true
case "$subcommand" in
  conformance)
    exec php "$ROOT_DIR/conformance_runner.php" "$@"
    ;;
  spec-runner)
    exec php "$ROOT_DIR/spec_runner.php" "$@"
    ;;
  *)
    echo "ERROR: unsupported subcommand: $subcommand" >&2
    echo "Usage: ./runner_adapter.sh {conformance|spec-runner} <args...>" >&2
    exit 2
    ;;
esac
