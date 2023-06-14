--TEST--
Test UnwindExit from die/exit is not exposed to userland code
--XFAIL--
UnwindExit is internal and should not be exposed to userland code. We need to decide whether to not run
post callback, or drop the UnwindExit and call the callback with null.
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
      //@todo whether this code should run or not (after die/exit) needs to be decided
      echo 'this code should not run';
    }
);

TestClass::run();
?>

--EXPECT--
exit!
