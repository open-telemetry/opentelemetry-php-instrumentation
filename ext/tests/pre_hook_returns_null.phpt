--TEST--
Check pre hook returning null does not modify params
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'hello',
    function($obj, array $params) {
        return null;
    }
);

function hello(string $val) {
    var_dump($val);
}

hello('original');
?>
--EXPECT--
string(8) "original"
