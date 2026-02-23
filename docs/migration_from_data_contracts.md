# Migration From data-contracts

This runner repo now owns PHP implementation details formerly documented in `data-contracts`.

Former locations in `data-contracts`:
- `docs/impl/php.md`
- `specs/impl/**` implementation narratives

Canonical runner-specific spec ownership now lives in:
- `data-contracts-library/specs/impl/php/`

This repository consumes that canonical source via:
- `/specs/upstream/data-contracts-library/specs/07_runner_behavior/impl/php/`
- `/specs/impl/php/index.md` (local pointer)
