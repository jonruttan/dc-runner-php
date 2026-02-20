#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

SPEC_FILE="$ROOT_DIR/specs/impl/php/runner_invariants_v1.md"
[[ -f "$SPEC_FILE" ]] || { echo "ERROR: invariant spec not found: $SPEC_FILE" >&2; exit 1; }

# 1) No synthetic text.file fixture fallback.
if rg -n "buildSyntheticTextFixture|synthetic library fixture|synthetic.noop" "$ROOT_DIR/conformance_runner.php" >/dev/null; then
  echo "ERROR: synthetic fixture fallback detected in conformance runner (violates runner_invariants_v1 #1)" >&2
  exit 1
fi

# 2) evaluate must enforce boolean.
if ! rg -n "evaluate expression must produce boolean" "$ROOT_DIR/conformance_runner.php" >/dev/null; then
  echo "ERROR: strict boolean evaluate guard missing (violates runner_invariants_v1 #2)" >&2
  exit 1
fi

# 3) governance must be non-success while unimplemented.
if ! rg -n "return 1;" "$ROOT_DIR/src/Cli/Command/GovernanceCommand.php" >/dev/null; then
  echo "ERROR: governance command does not fail while unimplemented (violates runner_invariants_v1 #3)" >&2
  exit 1
fi

# 4) critical coverage gate must be present in composer test-coverage.
if ! rg -n "check_critical_coverage\\.php" "$ROOT_DIR/composer.json" >/dev/null; then
  echo "ERROR: critical coverage gate missing from composer test-coverage (violates runner_invariants_v1 #4)" >&2
  exit 1
fi

# 5) do not advertise unsupported ops.job capability.
if rg -n "'ops\\.job'" "$ROOT_DIR/conformance_runner.php" >/dev/null; then
  echo "ERROR: ops.job capability is advertised without native support (violates runner_invariants_v1 #5)" >&2
  exit 1
fi

echo "OK: runner invariants verified"
