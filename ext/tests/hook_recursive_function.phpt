--TEST--
Check hooks work correctly with recursive functions
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'factorial',
    function($obj, array $params) {
        var_dump('pre: ' . $params[0]);
    },
    function($obj, array $params, mixed $ret) {
        var_dump('post: ' . $ret);
    }
);

function factorial(int $n): int {
    if ($n <= 1) return 1;
    return $n * factorial($n - 1);
}

var_dump(factorial(3));
?>
--EXPECT--
string(6) "pre: 3"
string(6) "pre: 2"
string(6) "pre: 1"
string(7) "post: 1"
string(7) "post: 2"
string(7) "post: 6"
int(6)
