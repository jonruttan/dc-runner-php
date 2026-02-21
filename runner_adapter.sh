#!/usr/bin/env bash
set -euo pipefail
ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
subcommand="${1:-}"
shift || true
case "$subcommand" in
  runner-certify)
    exec php "$ROOT_DIR/bin/spec-runner" runner-certify "$@"
    ;;
  governance)
    exec php "$ROOT_DIR/bin/spec-runner" governance "$@"
    ;;
  conformance)
    exec php "$ROOT_DIR/bin/spec-runner" conformance "$@"
    ;;
  spec-runner)
    exec php "$ROOT_DIR/bin/spec-runner" spec-runner "$@"
    ;;
  *)
    echo "ERROR: unsupported subcommand: $subcommand" >&2
    echo "Usage: ./runner_adapter.sh {runner-certify|governance|conformance|spec-runner} <args...>" >&2
    exit 2
    ;;
esac
