--TEST--
Test UnwindExit caused by async pcntl handler does not leak otel_exception_state memory
--SKIPIF--
<?php
if (PHP_OS_FAMILY === 'Windows' && PHP_VERSION_ID < 80100) {
    die("skip Required extension missing: pcntl"); // EXTENSIONS check not working properly on Windows 8.0 runner
}
?>
--EXTENSIONS--
opentelemetry
pcntl
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
        for (;;) {}
    }
);

pcntl_async_signals(true);
pcntl_signal(SIGALRM, static function() {
    echo "timeout\n";
    exit(1);
});

pcntl_alarm(1);
throwingFunction();
echo "fail\n";
?>

--EXPECT--
timeout
