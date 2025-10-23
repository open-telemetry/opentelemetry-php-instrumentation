# OpenTelemetry auto-instrumentation extension

[![Build and test](https://github.com/open-telemetry/opentelemetry-php-instrumentation/actions/workflows/build.yml/badge.svg)](https://github.com/open-telemetry/opentelemetry-php-instrumentation/actions/workflows/build.yml)

## Current Project Status
For more information, please consult the documentation of the main [OpenTelemetry PHP project](https://github.com/open-telemetry/opentelemetry-php).

## Issues

Issues have been disabled for this repo in order to help maintain consistency between this repo and the main [OpenTelemetry PHP project](https://github.com/open-telemetry/opentelemetry-php) repo. If you have an issue you'd like to raise about this issue, please use the [OpenTelemetry PHP Issue section](https://github.com/open-telemetry/opentelemetry-php/issues/new/choose). Please prefix the title of the issue with [opentelemetry-php-instrumentation].

## Description
This is a PHP extension for OpenTelemetry, to enable auto-instrumentation.
It is based on [zend_observer](https://www.datadoghq.com/blog/engineering/php-8-observability-baked-right-in/) and requires php8+

The extension allows:

- creating `pre` and `post` hook functions to arbitrary PHP functions and methods, which allows those methods to be wrapped with telemetry
- adding attributes to functions and methods to enable observers at runtime

In PHP 8.2+, internal/built-in PHP functions can also be observed.

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
install-php-extensions opentelemetry-php/ext-opentelemetry@main
```

Via pecl/pickle:
```shell
install-php-extensions opentelemetry[-beta|-stable|-latest]
```

### Windows

Pre-built windows binaries are available from the [releases page](https://github.com/open-telemetry/opentelemetry-php-instrumentation/releases)

See https://wiki.php.net/internals/windows/stepbystepbuild_sdk_2#building_pecl_extensions_with_phpize
for generic advice on building from source under Windows.

## Verify that the extension is installed and enabled

```shell
php --ri  opentelemetry
```

## Known issues

### Conflicting extensions

The extension can be configured to not run if a conflicting extension is installed. The following extensions
are known to not work when installed alongside OpenTelemetry:

* SourceGuardian

If the conflicting extension is a regular PHP extension (i.e, not a
[zend_extension](https://www.phpinternalsbook.com/php7/extensions_design/zend_extensions.html)), you can control
conflicts via the `opentelemetry.conflicts` ini setting.

If a conflicting extension is found, then the OpenTelemetry extension will disable itself:

```shell
php --ri opentelemetry

Notice: PHP Startup: Conflicting extension found (blackfire), disabling OpenTelemetry in Unknown on line 0

opentelemetry

opentelemetry hooks => disabled (conflict)
extension version => 1.0.0beta6

Directive => Local Value => Master Value
opentelemetry.conflicts => blackfire => blackfire
opentelemetry.validate_hook_functions => On => On
```

### Invalid pre/post hooks

Invalid argument types in `pre` and `post` callbacks can cause fatal errors. Runtime checking is performed on the
hook functions to ensure they are compatible. If not, the hook will not be executed and an error will be generated.

This feature can be disabled by setting the `opentelemetry.validate_hook_functions` ini value to `Off`;

### Increasing function argument count

By default, increasing the number of arguments provided to a function in the pre hook is allowed only if that does
not require the stack frame of the function call to be extended in size. For internal functions, adding arguments
not provided at the callsite always requires stack extension. For PHP functions, it is required only if the
argument is not included in the function definition.

Extending stack frame automatically can be enabled by setting `opentelemetry.allow_stack_extension` ini value to `On`.
This enables extending the stack frame by up to another 16 arguments.

## Usage

The `pre` method starts and activates a span. The `post` method ends the span after the observed method has finished.

```php
<?php

$tracer = new Tracer(...);

OpenTelemetry\Instrumentation\hook(
    DemoClass::class,
    'run',
    pre: static function (DemoClass $demo, array $params, string $class, string $function, ?string $filename, ?int $lineno) use ($tracer) {
        $span = $tracer->spanBuilder($class)
            ->startSpan();
        Context::storage()->attach($span->storeInContext(Context::getCurrent()));
    },
    post: static function (DemoClass $demo, array $params, $returnValue, ?Throwable $exception) use ($tracer) {
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

### Static methods

Note that if hooking a static class method, the first parameter to `pre` and `post` callbacks is a `string` containing the method's class name.

### Caveats

- Be aware that trivial functions are candidates for optimizations.
Optimizer can optimize them out and replace user function call with more optimal set of instructions (inlining).
In this case hooks will not be invoked as there will be no function.
- Hooks must be registered _before_ a function is first executed. You may encounter race conditions where
the composer autoloader runs code that uses functions you wish to hook prior to the hooks being registered.

# Modifying parameters, exceptions and return values of the observed function

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
\OpenTelemetry\Instrumentation\hook(null, 'hello', post: fn(mixed $object, array $params, string $return): int => ++$return);

function hello(int $val) {
    return $val;
}

var_dump(hello(1));
```

gives output:
```
int(2)
```

*Important*: the post method _must_ provide a return type-hint, otherwise the return value will be ignored. The return type
hint in the example above is `: int`.

## Exceptions

`post` hook methods can modify an exception thrown from the observed function:

```php
<?php
\OpenTelemetry\Instrumentation\hook(null, 'hello', post: function(mixed $object, array $params, mixed $return, ?Throwable $throwable) {
    throw new Exception('new', previous: $throwable);
});

function hello() {
    throw new Exception('original');
}

try {
    hello();
} catch (\Throwable $t) {
    var_dump($t->getMessage());
    var_dump($t->getPrevious()?->getMessage());
}
```

gives output:
```php
string(3) "new"
string(8) "original"
```

## Attribute-based hooking

By applying attributes to source code, the OpenTelemetry extension can add hooks at runtime.

Default pre and post hook methods are provided by the OpenTelemetry API: `OpenTelemetry\API\Instrumentation\Handler::pre`
and `::post`.

This feature is disabled by default, but can be enabled by setting `opentelemetry.attr_hooks_enabled = On` in php.ini

## Restrictions

Attribute-based hooks can only be applied to a function/method that does not already have
hooks applied.
Only one hook can be applied to a function/method, including via interfaces.

Since the attributes are evaluated at runtime, the extension checks whether a hook already
exists to decide whether it should apply a new runtime hook.

## Configuration

This feature can be configured via `.ini` by modifying the following entries:

- `opentelemetry.attr_hooks_enabled` - boolean, default Off
- `opentelemetry.attr_pre_handler_function` - FQN of pre method/function
- `opentelemetry.attr_post_handler_function` - FQN of post method/function

## `OpenTelemetry\API\Instrumentation\WithSpan` attribute

This attribute is provided by the OpenTelemetry API can be applied to a function or class method.

You can also provide optional parameters to the attribute, which control:
- span name
- span kind
- attributes

```php
use OpenTelemetry\API\Instrumentation\WithSpan

class MyClass
{
    #[WithSpan]
    public function trace_me(): void
    {
        /* ... */
    }

    #[WithSpan('custom_span_name', SpanKind::KIND_INTERNAL, ['my-attr' => 'value'])]
    public function trace_me_with_customization(): void
    {
        /* ... */
    }
}

#[WithSpan]
function my_function(): void
{
    /* ... */
}
```

## `OpenTelemetry\API\Instrumentation\SpanAttribute` attribute

This attribute should be used in conjunction with `WithSpan`. It is applied to function/method
parameters, and causes those parameters and values to be passed through to the `pre` hook function
where they can be added as trace attributes.
There is one optional parameter, which controls the attribute key. If not set, the parameter name
is used.

```php
use OpenTelemetry\API\Instrumentation\WithSpan
use OpenTelemetry\API\Instrumentation\SpanAttribute

class MyClass
{
    #[WithSpan]
    public function add_user(
        #[SpanAttribute] string $username,
        string $password,
        #[SpanAttribute('a_better_attribute_name')] string $foo_bar_baz,
    ): void
    {
        /* ... */
    }
```

## Contributing
See [DEVELOPMENT.md](DEVELOPMENT.md) and https://github.com/open-telemetry/opentelemetry-php/blob/main/CONTRIBUTING.md

### Maintainers

- [OpenTelemetry PHP Maintainers](https://github.com/open-telemetry/opentelemetry-php/blob/main/CONTRIBUTING.md#maintainers)

For more information about the maintainer role, see the [community repository](https://github.com/open-telemetry/community/blob/main/guides/contributor/membership.md#maintainer).

### Approvers

- [OpenTelemetry PHP Approvers](https://github.com/open-telemetry/opentelemetry-php/blob/main/CONTRIBUTING.md#approvers)

For more information about the approver role, see the [community repository](https://github.com/open-telemetry/community/blob/main/guides/contributor/membership.md#approver).
