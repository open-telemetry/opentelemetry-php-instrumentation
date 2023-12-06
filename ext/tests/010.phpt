--TEST--
Check if post hook can modify return value
--EXTENSIONS--
opentelemetry
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
