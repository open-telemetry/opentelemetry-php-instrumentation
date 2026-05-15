--TEST--
Check pre hook returning non-array value does not modify params
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'hello',
    function($obj, array $params) {
        return 'not an array';
    }
);

function hello(string $val) {
    var_dump($val);
}

hello('original');
?>
--EXPECT--
string(8) "original"
