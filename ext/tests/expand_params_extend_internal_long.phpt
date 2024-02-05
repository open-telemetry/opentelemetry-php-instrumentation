--TEST--
Check if pre hook can expand params of internal function when that requires extending the stack (many params)
--DESCRIPTION--
This will add MANY extra arguments to an internal function, making it fail with an error.
However, the purpose of this test is to just make sure it does not somehow corrupt the stack
and cause a crash with a large number of extra parameters.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80200) die('skip requires PHP >= 8.2'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.allow_stack_extension=On
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'array_slice',
    pre: function(null $instance, array $params) {
        return [$params[0], $params[1], 1, true, true, true, true, true, true, true, true, true, true];
    },
    post: fn() => null
);

var_dump(array_slice([1,2,3], 1));
?>
--EXPECTF--
Fatal error: Uncaught ArgumentCountError: array_slice() expects at most 4 arguments, 13 given in %s
Stack trace:
#0 %s: array_slice(Array, 1, 1, true, true, true, true, true, true, true, true, true, true)
#1 {main}
  thrown in %s on line %d