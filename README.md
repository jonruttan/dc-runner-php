# dc-runner-php

PHP compatibility runner for Data Contracts.

## Scope

- Owns PHP compatibility execution surfaces formerly hosted in monorepo path `runners/php`.
- Non-blocking lane relative to required Rust lane.

## Commands

- `php conformance_runner.php --cases specs/conformance/cases --case-formats md --out .artifacts/conformance-parity-php.json`
- `php spec_runner.php --cases specs/impl/php/cases --case-formats md --out .artifacts/php-spec-runner.json`

## Adapter

- `./runner_adapter.sh conformance ...`
- `./runner_adapter.sh spec-runner ...`
