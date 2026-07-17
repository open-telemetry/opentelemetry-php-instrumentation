--TEST--
Check hook on function with default parameters
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    function($obj, array $params) {
        var_dump($params);
    }
);

function helloWorld(string $a, string $b = 'default_b', string $c = 'default_c') {
    var_dump($a, $b, $c);
}

helloWorld('value_a');
?>
--EXPECT--
array(1) {
  [0]=>
  string(7) "value_a"
}
string(7) "value_a"
string(9) "default_b"
string(9) "default_c"
