--TEST--
Test UnwindExit from die/exit is not exposed to userland code
--EXTENSIONS--
opentelemetry
--FILE--

<?php

use function OpenTelemetry\Instrumentation\hook;

class TestClass {
    public static function run(): void
    {
       die('exit!');
    }
}

hook(
    'TestClass',
    'run',
    null,
    static function ($object, array $params, mixed $ret, ?\Throwable $exception ) {
      echo PHP_EOL . 'post';
    }
);

TestClass::run();
?>

--EXPECT--
exit!
post
