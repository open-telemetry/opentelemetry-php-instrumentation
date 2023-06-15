--TEST--
Check if hooks receive modified exception
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: fn(mixed $object, array $params, mixed $return, Exception $throwable) => throw new Exception(previous: $throwable));
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', post: fn(mixed $object, array $params, mixed $return, Exception $throwable) => throw new Exception(previous: $throwable));

function helloWorld() {
    throw new Exception('original');
}

try {
    helloWorld();
} catch (Exception $e) {
    var_dump($e->getPrevious()?->getPrevious()?->getMessage());
}
?>
--EXPECT--
string(8) "original"
