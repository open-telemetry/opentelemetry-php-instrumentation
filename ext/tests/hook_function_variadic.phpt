--TEST--
Check hook on function with variadic parameters
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    function($obj, array $params) {
        var_dump(count($params));
    },
    function($obj, array $params, mixed $ret) {
        var_dump(count($params));
    }
);

function helloWorld(string ...$args) {
    return implode(',', $args);
}

helloWorld('a', 'b', 'c', 'd');
?>
--EXPECT--
int(4)
int(4)
