--TEST--
Check if hook can modify not provided arguments
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn() => [1 => 'b']);

function helloWorld($a = null, $b = null) {
    var_dump($a, $b);
}

helloWorld();
?>
--EXPECT--
NULL
string(1) "b"
