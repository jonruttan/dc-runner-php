# Fixture

```yaml contract-spec
spec_version: 1
schema_ref: /specs/schema/schema_v1.md
defaults: { type: contract.check }
contracts:
  - id: T-1
    harness: { check: { profile: text.file, config: {} } }
    clauses: { defaults: {}, predicates: [] }
```