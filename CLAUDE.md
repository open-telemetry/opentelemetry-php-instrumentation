# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What This Is

A PHP extension (written in C) that provides auto-instrumentation capabilities for OpenTelemetry PHP. It uses the Zend Observer API to hook into PHP function calls, enabling pre/post callbacks on arbitrary PHP functions and methods. The single public API is `OpenTelemetry\Instrumentation\hook()`.

## Build Commands

Development uses Docker. The top-level `Makefile` wraps docker compose commands:

```shell
make build-image          # Build Docker image (debian by default)
make shell                # Shell into container
make build                # Build extension in container
make test                 # Run all tests in container
make format               # Run clang-format (debian only)
make all                  # Build image + build + test
```

Inside the container (or locally with php-dev installed):
```shell
cd ext/
phpize
./configure
make
make test
make install
```

Run a single test:
```shell
make test TESTS=tests/001.phpt
```

Run tests with valgrind (inside container):
```shell
php run-tests.php -d extension=$(pwd)/modules/opentelemetry.so -m
php run-tests.php -d extension=$(pwd)/modules/opentelemetry.so -m tests/<name>.phpt
```

## Code Formatting

All C code must pass `clang-format-18`. Run `make format` before committing. CI enforces this via `.github/workflows/check-style.yml`.

## Architecture

- **`ext/opentelemetry.c`** — Extension module init/shutdown, PHP function registration, ini settings. Entry point for the extension lifecycle.
- **`ext/otel_observer.c`** / **`otel_observer.h`** — Core logic: registers Zend observer handlers for hooked functions, manages pre/post callback execution, parameter/return value modification.
- **`ext/php_opentelemetry.h`** — Module globals definition.
- **`ext/opentelemetry.stub.php`** — PHP stub defining the `hook()` function signature. Used to generate `opentelemetry_arginfo.h`.
- **`ext/tests/`** — PHPT test files (PHP's native test format). Each `.phpt` file contains test code and expected output.

## Testing

Tests use PHP's PHPT format. When a test fails, a `.sh` script is generated that can re-run it in isolation:
```shell
tests/name_of_test.sh           # run test
tests/name_of_test.sh gdb       # run with gdb
tests/name_of_test.sh valgrind  # run with valgrind
```

## CI Matrix

Tests run on Linux, macOS, and Windows across PHP 8.1–8.4 (plus 8.5 nightly). See `.github/workflows/build.yml`.

## Compilation Flags

The extension compiles with `-Wall -Wextra -Werror -Wno-unused-parameter` (defined in `ext/config.m4`).
