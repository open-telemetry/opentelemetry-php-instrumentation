--TEST--
Check if hooks receive modified arguments
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn(mixed $object, array $params) => [++$params[0]]);
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn(mixed $object, array $params) => [++$params[0]]);

function helloWorld($a) {
    var_dump($a);
}

helloWorld(1);
?>
--EXPECT--
int(3)
