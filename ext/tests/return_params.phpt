--TEST--
Check if pre hook can return $params
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn($obj, array $params) => $params);

function helloWorld($a) {
    var_dump($a);
}

helloWorld('a');
?>
--EXPECT--
string(1) "a"
