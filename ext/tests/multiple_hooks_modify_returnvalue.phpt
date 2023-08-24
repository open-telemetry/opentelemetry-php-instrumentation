--TEST--
Check if hooks receive modified return value
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: fn(mixed $object, array $params, string $return): int => ++$return);
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: fn(mixed $object, array $params, string $return): int => ++$return);

function helloWorld(int $val) {
    return $val;
}

var_dump(helloWorld(1));
?>
--EXPECT--
int(3)
