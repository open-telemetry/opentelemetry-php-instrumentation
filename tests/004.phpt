--TEST--
Check if multiple hooks are invoked
--EXTENSIONS--
otel_instrumentation
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn() => var_dump('PRE_1'), fn() => var_dump('POST_1'));
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn() => var_dump('PRE_2'), fn() => var_dump('POST_2'));

function helloWorld() {
    var_dump('CALL');
}

helloWorld();
?>
--EXPECT--
string(5) "PRE_1"
string(5) "PRE_2"
string(4) "CALL"
string(6) "POST_2"
string(6) "POST_1"
