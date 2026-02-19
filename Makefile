.PHONY: help lint smoke
.DEFAULT_GOAL := help

help:
	@echo "lint  - php syntax checks"
	@echo "smoke - run help output for both entrypoints"

lint:
	php -l conformance_runner.php
	php -l spec_runner.php

smoke:
	php conformance_runner.php --help || true
	php spec_runner.php --help || true
