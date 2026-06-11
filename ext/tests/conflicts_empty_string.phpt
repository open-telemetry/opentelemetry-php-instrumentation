--TEST--
Check no conflict when conflicts is empty string
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.conflicts=
--FILE--
<?php
var_dump(extension_loaded('opentelemetry'));
var_dump(\OpenTelemetry\Instrumentation\hook(null, 'test_func', fn() => var_dump('PRE')));

function test_func() {
    var_dump('CALL');
}

test_func();
?>
--EXPECT--
bool(true)
bool(true)
string(3) "PRE"
string(4) "CALL"
