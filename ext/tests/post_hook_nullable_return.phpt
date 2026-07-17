--TEST--
Check post hook can return null to replace return value when return type is nullable
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: function(mixed $object, array $params, ?string $return): ?string {
    return null;
});

function helloWorld(string $val): ?string {
    return $val;
}

var_dump(helloWorld('foo'));
?>
--EXPECT--
NULL
