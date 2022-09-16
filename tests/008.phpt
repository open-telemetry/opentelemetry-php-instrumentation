--TEST--
Check if hook can modify arguments
--EXTENSIONS--
otel_instrumentation
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn() => ['b']);

function helloWorld($a) {
    var_dump($a);
}

helloWorld('a');
?>
--EXPECT--
string(1) "b"
