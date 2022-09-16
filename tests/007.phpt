--TEST--
Check if hook receives exception
--EXTENSIONS--
otel_instrumentation
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    null,
    fn(object|string|null $scope, array $params, mixed $returnValue, ?Throwable $throwable) => var_dump($throwable?->getMessage()));

function helloWorld() {
    throw new Exception('error');
}

try {
    helloWorld();
} catch (Exception $e) {}
?>
--EXPECT--
string(5) "error"
