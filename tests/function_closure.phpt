--TEST--
Check if hooks are invoked for closures
--EXTENSIONS--
otel_instrumentation
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn() => var_dump('PRE'), fn() => var_dump('POST'));

function helloWorld() {
    var_dump('HELLO');
}

Closure::fromCallable('helloWorld')();
?>
--EXPECT--
string(3) "PRE"
string(5) "HELLO"
string(4) "POST"
