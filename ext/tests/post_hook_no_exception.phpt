--TEST--
Check post hook receives null exception when function succeeds
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    null,
    function($obj, array $params, mixed $ret, ?Throwable $e) {
        var_dump($ret);
        var_dump($e);
    }
);

function helloWorld(): string {
    return 'success';
}

helloWorld();
?>
--EXPECT--
string(7) "success"
NULL
