.PHONY: help lint smoke test test-coverage spec-sync spec-sync-check compat-check runner-spec-sync runner-spec-check runner-invariants-check verify
.DEFAULT_GOAL := help
SOURCE ?=

help: ## Display this help section
	@awk 'BEGIN {FS = ":.*?## "}; /^##@/ {printf "\n\033[33m%s\033[0m\n", substr($$0,5)}; /^[a-zA-Z0-9_-]+:.*?## / {printf "  \033[32m%-36s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

##@ Core
lint: ## Run php syntax checks
	php -l conformance_runner.php
	php -l spec_runner.php

smoke: ## Run help output for both entrypoints
	./runner_adapter.sh conformance --help
	./runner_adapter.sh spec-runner --help

test: ## Run PHPUnit unit/integration tests (requires composer install)
	@test -f vendor/autoload.php || ./scripts/composer.sh install --no-interaction --prefer-dist
	./vendor/bin/phpunit --configuration phpunit.xml

test-coverage: ## Run PHPUnit with text coverage report
	@test -f vendor/autoload.php || ./scripts/composer.sh install --no-interaction --prefer-dist
	bash scripts/run_phpunit_coverage.sh
	php scripts/check_critical_coverage.php .artifacts/coverage/clover.xml

##@ Specs
spec-sync: ## Sync pinned upstream specs snapshot (TAG required)
	@test -n "$(TAG)" || (echo "ERROR: TAG is required (make spec-sync TAG=<upstream-tag>)" >&2; exit 2)
	@if [ -n "$(SOURCE)" ]; then \
		./scripts/sync_data_contracts_specs.sh --tag "$(TAG)" --source "$(SOURCE)"; \
	else \
		./scripts/sync_data_contracts_specs.sh --tag "$(TAG)"; \
	fi

spec-sync-check: ## Verify upstream lock/snapshot integrity
	@if [ -n "$(SOURCE)" ]; then \
		./scripts/sync_data_contracts_specs.sh --check --source "$(SOURCE)"; \
	else \
		./scripts/sync_data_contracts_specs.sh --check; \
	fi

compat-check: ## Verify runner compatibility against pinned upstream snapshot
	@if [ -n "$(SOURCE)" ]; then \
		./scripts/verify_upstream_compat.sh --strict --source "$(SOURCE)"; \
	else \
		./scripts/verify_upstream_compat.sh --strict; \
	fi

runner-spec-sync: ## Sync pinned runner-specific specs snapshot (TAG required)
	@test -n "$(TAG)" || (echo "ERROR: TAG is required (make runner-spec-sync TAG=<upstream-tag>)" >&2; exit 2)
	@if [ -n "$(SOURCE)" ]; then \
		./scripts/sync_runner_specs.sh --tag "$(TAG)" --source "$(SOURCE)"; \
	else \
		./scripts/sync_runner_specs.sh --tag "$(TAG)"; \
	fi

runner-spec-check: ## Verify runner-specific specs lock/snapshot integrity
	@if [ -n "$(SOURCE)" ]; then \
		./scripts/verify_runner_specs.sh --source "$(SOURCE)"; \
	else \
		./scripts/verify_runner_specs.sh; \
	fi

runner-invariants-check: ## Verify local runner invariants from specs/impl/php
	./scripts/verify_runner_invariants.sh

##@ Aggregate
verify: lint smoke test spec-sync-check compat-check runner-spec-check runner-invariants-check ## Run blocking local verification suite
