# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

PHP C extension for OpenTelemetry auto-instrumentation. Uses PHP 8's `zend_observer` API to create pre/post hook functions for arbitrary PHP functions and methods. The extension is distributed via PECL as `open-telemetry/ext-opentelemetry`.

## Build & Test Commands

All commands use Docker by default. Set `PHP_VERSION` (default 8.3.10) and `DISTRO` (debian/alpine) as needed.

### Docker-based (primary workflow)
```bash
make build-image          # Build Docker image
make build                # Build extension via Docker
make test                 # Run all tests (PHPT format)
make format               # Run clang-format (debian only)
make shell                # Interactive shell in Docker container
```

### Inside Docker container (or local with PHP debug build)
```bash
phpize && ./configure && make    # Build from source
make test TESTS=--show-diff      # Run all tests with diff output

# Run a single test:
php run-tests.php -d extension=$(pwd)/modules/opentelemetry.so tests/<name>.phpt

# Run with valgrind:
php run-tests.php -d extension=$(pwd)/modules/opentelemetry.so -m tests/<name>.phpt
```

### Code formatting
Run `make format` before committing. Uses clang-format (v16/v18) on `ext/*.c ext/*.h`. CI enforces this via `check-style.yml`.

## Architecture

### Extension Source (`ext/`)
- **`opentelemetry.c`** — Module entry point: MINIT/RINIT/RSHUTDOWN/MSHUTDOWN lifecycle, INI settings, conflict detection, PHP function registration
- **`otel_observer.c`** — Core logic (~1200 lines): zend_observer integration, hook matching, pre/post callback invocation, parameter/return value manipulation
- **`php_opentelemetry.h`** — Module globals (observer lookup HashTables, INI-backed flags)
- **`opentelemetry_arginfo.h`** — Generated argument info for the `hook()` function

### PHP API
Single function: `OpenTelemetry\Instrumentation\hook(?string $class, string $function, ?Closure $pre, ?Closure $post): bool`

### Tests (`ext/tests/`)
75 PHPT files using PHP's native test format. Each test has `--TEST--`, `--EXTENSIONS--`, `--FILE--`, and `--EXPECT--`/`--EXPECTF--` sections.

### Build System
- Unix: autoconf via `config.m4` (compile flags: `-Wall -Wextra -Werror -Wno-unused-parameter`)
- Windows: `config.w32` with nmake
- PECL packaging: `package.xml`

### CI Matrix
- Linux + macOS: PHP 8.1–8.4
- Windows: PHP 8.1–8.4 (TS and NTS variants)
- Nightly: PHP 8.5

## Key INI Settings
- `opentelemetry.conflicts` — Comma-separated conflicting extensions (e.g., SourceGuardian, Blackfire)
- `opentelemetry.validate_hook_functions` — Runtime validation of hook closures
- `opentelemetry.allow_stack_extension` — Allow extending function stack frame
- `opentelemetry.attr_hooks_enabled` — Enable attribute-based hooking (`#[WithSpan]`, `#[SpanAttribute]`)
