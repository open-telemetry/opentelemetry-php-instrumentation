--TEST--
Check if hooks are invoked for first class callables
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP8.1'); ?>
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn() => var_dump('PRE'), fn() => var_dump('POST'));

function helloWorld() {
    var_dump('HELLO');
}

helloWorld(...)();
?>
--EXPECT--
string(3) "PRE"
string(5) "HELLO"
string(4) "POST"
