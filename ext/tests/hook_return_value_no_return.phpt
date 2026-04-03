--TEST--
Check post hook receives null return value for void function
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    null,
    function($obj, array $params, mixed $ret) {
        var_dump($ret);
    }
);

function helloWorld(): void {
    // no return
}

helloWorld();
?>
--EXPECT--
NULL
