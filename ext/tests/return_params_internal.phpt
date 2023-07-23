--TEST--
Check if pre hook can return $params for internal function
--SKIPIF--
<?php if (PHP_VERSION_ID < 80200) die('skip requires PHP >= 8.2'); ?>
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'array_map', fn($obj, array $params) => $params);

array_map(var_dump(...), ['HELLO']);
?>
--EXPECT--
string(5) "HELLO"
