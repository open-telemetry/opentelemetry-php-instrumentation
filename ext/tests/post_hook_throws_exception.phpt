--TEST--
Check if throwing an exception in post hook after IO operation will finish gracefully
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    fn() => var_dump('pre'),
    fn(object|string|null $scope, array $params, mixed $returnValue, ?Throwable $throwable) => throw new Exception('error'));


function helloWorld() {
  // below scandir call or any other
  // IO operation is necessary to trigger
  // segfault.
  scandir(".");
}

try {
helloWorld();
} catch(Exception) {}

--EXPECT--
string(3) "pre"

