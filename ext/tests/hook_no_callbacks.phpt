--TEST--
Check hook with no pre or post callbacks returns true but does nothing
--EXTENSIONS--
opentelemetry
--FILE--
<?php
var_dump(\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', null, null));

function helloWorld() {
    var_dump('CALL');
}

helloWorld();
?>
--EXPECT--
bool(true)
string(4) "CALL"
