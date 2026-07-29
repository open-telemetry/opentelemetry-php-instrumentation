--TEST--
Check post hook replaces return value when it has a non-void return type
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: function(mixed $object, array $params, string $return): string {
    return 'replaced';
});

function helloWorld(string $val): string {
    return $val;
}

var_dump(helloWorld('original'));
?>
--EXPECT--
string(8) "replaced"
