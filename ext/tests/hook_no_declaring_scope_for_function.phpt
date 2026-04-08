--TEST--
Check declaring scope is null for non-class functions
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'hello',
    function($obj, array $params, ?string $class, string $function) {
        var_dump($obj);
        var_dump($class);
    }
);

function hello(): void {}

hello();
?>
--EXPECT--
NULL
NULL
