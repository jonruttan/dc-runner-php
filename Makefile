.PHONY: help lint smoke spec-sync spec-sync-check compat-check runner-spec-sync runner-spec-check verify
.DEFAULT_GOAL := help
SOURCE ?=

help:
	@echo "lint  - php syntax checks"
	@echo "smoke - run help output for both entrypoints"
	@echo "spec-sync TAG=<upstream-tag> [SOURCE=<path-or-url>] - sync pinned upstream specs snapshot"
	@echo "spec-sync-check [SOURCE=<path-or-url>] - verify upstream lock/snapshot integrity"
	@echo "compat-check [SOURCE=<path-or-url>] - verify runner compatibility against pinned upstream snapshot"
	@echo "runner-spec-sync TAG=<upstream-tag> [SOURCE=<path-or-url>] - sync pinned runner-specific specs snapshot"
	@echo "runner-spec-check [SOURCE=<path-or-url>] - verify runner-specific specs lock/snapshot integrity"
	@echo "verify - lint + smoke + spec-sync-check + compat-check + runner-spec-check"

lint:
	php -l conformance_runner.php
	php -l spec_runner.php

smoke:
	./runner_adapter.sh conformance --help
	./runner_adapter.sh spec-runner --help

spec-sync:
	@test -n "$(TAG)" || (echo "ERROR: TAG is required (make spec-sync TAG=<upstream-tag>)" >&2; exit 2)
	@if [ -n "$(SOURCE)" ]; then \
		./scripts/sync_data_contracts_specs.sh --tag "$(TAG)" --source "$(SOURCE)"; \
	else \
		./scripts/sync_data_contracts_specs.sh --tag "$(TAG)"; \
	fi

spec-sync-check:
	@if [ -n "$(SOURCE)" ]; then \
		./scripts/sync_data_contracts_specs.sh --check --source "$(SOURCE)"; \
	else \
		./scripts/sync_data_contracts_specs.sh --check; \
	fi

compat-check:
	@if [ -n "$(SOURCE)" ]; then \
		./scripts/verify_upstream_compat.sh --strict --source "$(SOURCE)"; \
	else \
		./scripts/verify_upstream_compat.sh --strict; \
	fi

runner-spec-sync:
	@test -n "$(TAG)" || (echo "ERROR: TAG is required (make runner-spec-sync TAG=<upstream-tag>)" >&2; exit 2)
	@if [ -n "$(SOURCE)" ]; then \
		./scripts/sync_runner_specs.sh --tag "$(TAG)" --source "$(SOURCE)"; \
	else \
		./scripts/sync_runner_specs.sh --tag "$(TAG)"; \
	fi

runner-spec-check:
	@if [ -n "$(SOURCE)" ]; then \
		./scripts/verify_runner_specs.sh --source "$(SOURCE)"; \
	else \
		./scripts/verify_runner_specs.sh; \
	fi

verify: lint smoke spec-sync-check compat-check runner-spec-check
