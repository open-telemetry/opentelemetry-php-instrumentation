--TEST--
Check pre hook modifying a single positional parameter
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'hello',
    function($obj, array $params) {
        return ['modified'];
    }
);

function hello(string $val) {
    var_dump($val);
}

hello('original');
?>
--EXPECT--
string(8) "modified"
