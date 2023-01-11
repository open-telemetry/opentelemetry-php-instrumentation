# OpenTelemetry auto-instrumentation extension

[![Build and test](https://github.com/open-telemetry/opentelemetry-php-instrumentation/actions/workflows/build.yml/badge.svg)](https://github.com/open-telemetry/opentelemetry-php-instrumentation/actions/workflows/build.yml)

This is an _experimental_ extension for OpenTelemetry, to enable auto-instrumentation.
It is based on [zend_observer](https://www.datadoghq.com/blog/engineering/php-8-observability-baked-right-in/) and requires php8+

The extension allows creating `pre` and `post` hook functions to arbitrary PHP functions and methods, which allows those methods to be wrapped with telemetry. 

## Requirements
- PHP 8+
- [OpenTelemetry PHP library](https://github.com/open-telemetry/opentelemetry-php)

## Installation

1. Install the extension via [pickle](https://github.com/FriendsOfPHP/pickle) or [php-extension-installer](https://github.com/mlocati/docker-php-extension-installer) (docker specific):

    * **pickle** can be used to install extensions that are available via http://pecl.php.net, however that's not the case
    for opentelemetry-php-instrumentation yet, so the only way for it is to install directly from source code.
    Following command line shows how to do that using specific version of extension (1.0.0beta1 in this case).

        ```console
        $ php pickle.phar install --source https://github.com/open-telemetry/opentelemetry-php-instrumentation.git#1.0.0beta1
        ```

   *  **php-extension-installer**
        ```console
        $ install-php-extensions open-telemetry/opentelemetry-php-instrumentation@main
        ```

2. Verify that the extension is installed and enabled:

   ```console
   $ php -m | grep  otel_instrumentation
   ```

## Usage

The following example adds an observer to the `DemoClass::run` method, and provides two functions which will be run before and after the method call.

The `pre` method starts and activates a span. The `post` method ends the span after the observed method has finished.

#### Warning
Be aware of that, trivial functions are candidates for optimizations.
Optimizer can optimize them out and replace user function call with more optimal set of instructions (inlining).
In this case hooks will not be invoked as there will be no function.


```php
<?php

$tracer = new Tracer(...);

OpenTelemetry\Instrumentation\hook(
    DemoClass::class,
    'run',
    static function (DemoClass $demo, array $params, string $class, string $function, ?string $filename, ?int $lineno) use ($tracer) {
        $tracer->spanBuilder($class)
            ->startSpan()
            ->activate();
    },
    static function (DemoClass $demo, array $params, $returnValue, ?Throwable $exception) use ($tracer) {
        $scope = Context::storage()->scope();
        $scope?->detach();
        $span = Span::fromContext($scope->context());
        $exception && $span->recordException($exception);
        $span->setStatus($exception ? StatusCode::STATUS_ERROR : StatusCode::STATUS_OK);
        $span->end();
    }
);
```

There are more examples in the [tests directory](./tests/)

## Contributing
See [DEVELOPMENT.md](DEVELOPMENT.md) and https://github.com/open-telemetry/opentelemetry-php/blob/main/CONTRIBUTING.md
