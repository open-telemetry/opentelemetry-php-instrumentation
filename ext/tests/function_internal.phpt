--TEST--
Check if hooks are invoked for internal functions
--SKIPIF--
<?php if (PHP_VERSION_ID < 80200) die('skip requires PHP >= 8.2'); ?>
<?php if (PHP_VERSION_ID >= 80600) die('skip internal-function hooks do not fire on PHP >= 8.6'); ?>
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'array_map', fn() => var_dump('PRE'), fn() => var_dump('POST'));

array_map(var_dump(...), ['HELLO']);
?>
--EXPECT--
string(3) "PRE"
string(5) "HELLO"
string(4) "POST"
