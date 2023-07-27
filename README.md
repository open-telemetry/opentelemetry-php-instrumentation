# OpenTelemetry auto-instrumentation extension

[![Build and test](https://github.com/open-telemetry/opentelemetry-php-instrumentation/actions/workflows/build.yml/badge.svg)](https://github.com/open-telemetry/opentelemetry-php-instrumentation/actions/workflows/build.yml)

This is a PHP extension for OpenTelemetry, to enable auto-instrumentation.
It is based on [zend_observer](https://www.datadoghq.com/blog/engineering/php-8-observability-baked-right-in/) and requires php8+

The extension allows creating `pre` and `post` hook functions to arbitrary PHP functions and methods, which allows those methods to be wrapped with telemetry. 

## Requirements
- PHP 8+
- [OpenTelemetry PHP library](https://github.com/open-telemetry/opentelemetry-php)

## Installation

The extension can be installed in all of the usual ways:

### pecl

```shell
pecl install opentelemetry
```

### php-extension-installer

If you are using the [official PHP docker images](https://hub.docker.com/_/php) then you can use
[php-extension-installer](https://github.com/mlocati/docker-php-extension-installer)

From github:
```shell
install-php-extensions open-telemetry/opentelemetry-php-instrumentation@main
```

Via pecl/pickle:
```shell
install-php-extensions opentelemetry[-beta|-stable|-latest]
```

## Verify that the extension is installed and enabled

```shell
php -m | grep  opentelemetry
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
        $span = $tracer->spanBuilder($class)
            ->startSpan();
        Context::storage()->attach($span->storeInContext(Context::getCurrent()));
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

There are more examples in the [tests directory](ext/tests/)

# Modifying parameters and return values of the observed function

## Parameters

From a `pre` hook function, you may modify the parameters before they are received by the observed function.
The arguments are passed in as a numerically-indexed array. The returned array from the `pre` hook is used
to modify (_not_ replace) the existing parameters:

```php
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'hello',
     function($obj, array $params) {
        return [
          0 => null,  //make first param null
          2 => 'baz', //replace 3rd param
          3 => 'bat', //add 4th param
        ];
    }
);
function hello($one = null, $two = null, $three = null, $four = null) {
  var_dump(func_get_args());
}

hello('a', 'b', 'c');
```

gives output:
```
array(4) {
  [0]=>
  NULL
  [1]=>
  string(1) "b"
  [2]=>
  string(3) "baz"
  [3]=>
  string(3) "bat"
}
```

## Return values

`post` hook methods can modify the observed function's return value:

```php
<?php
<?php
\OpenTelemetry\Instrumentation\hook(null, 'hello', post: fn(mixed $object, array $params, string $return): int => ++$return);

function helloWorld(int $val) {
    return $val;
}

var_dump(hello(1));
```

gives output:
```
int(3)
```

*Important*: the post method _must_ provide a return type-hint, otherwise the return value will be ignored.

## Contributing
See [DEVELOPMENT.md](DEVELOPMENT.md) and https://github.com/open-telemetry/opentelemetry-php/blob/main/CONTRIBUTING.md
