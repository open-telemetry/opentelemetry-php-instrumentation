--TEST--
Check if pre hook trying to modify extra params of internal functions crashes
--DESCRIPTION--
Modifying extra parameters of internal functions is actually not useful at all since providing too many parameters
to an internal function always causes an Error to be thrown anyway. However, the test is still needed to make sure it
does not crash.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80200) die('skip requires PHP >= 8.2'); ?>
--EXTENSIONS--
opentelemetry
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'array_slice',
    pre: function(null $instance, array $params) {
        return [$params[0], $params[1], 2, false, 'a'];
    },
    post: fn() => null
);

try {
    var_dump(array_slice([1,2,3], 1, 1, false, 'a'));
} catch (Throwable $t) {
    var_dump($t->getMessage());
}
?>
--EXPECT--
string(50) "array_slice() expects at most 4 arguments, 5 given"