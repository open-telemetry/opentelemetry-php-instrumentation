--TEST--
Check if pre hook can expand then return $params
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', function($obj, array $params) {
    $params[1] = 'b';
    return $params;
});

function helloWorld($a, $b = null) {
    var_dump($a);
    var_dump($b);
}

helloWorld('a');
?>
--EXPECT--
string(1) "a"
string(1) "b"
