--TEST--
Test UnwindExit caused by async pcntl handler is not suppressed
--EXTENSIONS--
opentelemetry
pcntl
--FILE--
<?php

use function OpenTelemetry\Instrumentation\hook;

hook(
    null,
    'sleep',
    static fn() => null,
    static fn() => null,
);

pcntl_async_signals(true);
pcntl_signal(SIGALRM, static function() {
    echo "timeout\n";
    exit(1);
});

pcntl_alarm(1);
sleep(2);
echo "fail\n";
?>

--EXPECT--
timeout
