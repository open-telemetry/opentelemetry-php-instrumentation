--TEST--
Check if hook can modify return value
--EXTENSIONS--
otel_instrumentation
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', null, fn(): int => 17);

function helloWorld() {
    return 42;
}

var_dump(helloWorld());
?>
--EXPECT--
int(17)
