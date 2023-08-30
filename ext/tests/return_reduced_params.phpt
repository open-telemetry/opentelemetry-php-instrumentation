--TEST--
Check if pre hook can reduce then return $params
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', function($obj, array $params) {
    $params[1] = null;
    return $params;
});

function helloWorld($a = null, $b = null, $c = null) {
    var_dump($a, $b, $c);
}

helloWorld('a', 'b', 'c');
?>
--EXPECT--
string(1) "a"
NULL
string(1) "c"
