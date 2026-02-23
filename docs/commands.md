# Commands

## Core

```sh
make lint
make smoke
make verify
```

## Global Specs (`data-contracts`)

```sh
make spec-sync TAG=<tag-or-ref> SOURCE=<path-or-url>
make spec-sync-check
make compat-check
```

## Runner Specs (`data-contracts-library`)

```sh
make runner-spec-sync TAG=<tag-or-ref> SOURCE=<path-or-url>
make runner-spec-check
```
