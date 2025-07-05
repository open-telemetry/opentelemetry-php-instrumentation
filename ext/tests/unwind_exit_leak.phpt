--TEST--
Test UnwindExit in post handler does not leak otel_exception_state memory
--EXTENSIONS--
opentelemetry
--FILE--
<?php

use function OpenTelemetry\Instrumentation\hook;

function throwingFunction() {
    throw new Exception();
}

hook(
    null,
    'throwingFunction',
    static fn() => null,
    static function() {
        exit;
    }
);


throwingFunction();
echo "fail\n";
?>

--EXPECT--
