--TEST--
Check if hooks receive modified arguments
--EXTENSIONS--
otel_instrumentation
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn(mixed $object, array $params) => [++$params[0]]);
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn(mixed $object, array $params) => [++$params[0]]);

function helloWorld($a) {
    var_dump($a);
}

helloWorld('a');
?>
--EXPECT--
string(1) "c"
