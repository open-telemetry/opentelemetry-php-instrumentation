--TEST--
Check if pre hook can expand params of internal function when that requires extending the stack
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
        return [$params[0], $params[1], 1, true];
    },
    post: fn() => null
);

var_dump(array_slice([1,2,3], 1));
?>
--EXPECTF--
array(1) {
  [1]=>
  int(2)
}
