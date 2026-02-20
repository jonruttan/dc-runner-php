# PHP Runner Invariants v1

These invariants define local compatibility-lane guardrails for this repository.

## Invariants

1. `text.file` subject loading must not synthesize content when fixture files are missing.
   Missing fixtures must fail as runtime/tool errors.

2. `evaluate` assertions must produce boolean results.
   Non-boolean evaluated values must fail deterministically as schema errors.

3. Governance command must not report success when unimplemented.
   `bin/spec-runner governance` must exit non-zero until implemented.

4. Critical-path unit coverage must be enforced by the verification gate.
   `composer test-coverage` must run `scripts/check_critical_coverage.php`.

5. Capability declarations must not claim unsupported `contract.job` execution.
   `ops.job` capability must not be advertised unless executable.
