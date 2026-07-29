--TEST--
Check post hook with void return type does not modify return value
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: function(mixed $object, array $params, string $return): void {
    // void return type means return value should not be modified
});

function helloWorld(string $val): string {
    return $val;
}

var_dump(helloWorld('foo'));
?>
--EXPECT--
string(3) "foo"
