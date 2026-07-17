--TEST--
Check no conflict when listed extensions are not loaded
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.conflicts=nonexistent1,nonexistent2
--FILE--
<?php
var_dump(extension_loaded('opentelemetry'));
$ret = \OpenTelemetry\Instrumentation\hook(null, 'test_func', fn() => var_dump('PRE'));

function test_func() {
    var_dump('CALL');
}

test_func();
?>
--EXPECT--
bool(true)
string(3) "PRE"
string(4) "CALL"
