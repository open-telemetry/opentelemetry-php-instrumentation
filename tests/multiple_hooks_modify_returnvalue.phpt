--TEST--
Check if hooks receive modified returnvalue
--EXTENSIONS--
otel_instrumentation
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: fn(mixed $object, array $params, string $return): string => ++$return);
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: fn(mixed $object, array $params, string $return): string => ++$return);

function helloWorld() {
    return 'a';
}

var_dump(helloWorld());
?>
--XFAIL--
Return value modifications are not propagated between callbacks.
--EXPECT--
string(1) "c"
