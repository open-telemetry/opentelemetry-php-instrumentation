--TEST--
Check hook on function that never gets defined does not error
--EXTENSIONS--
opentelemetry
--FILE--
<?php
var_dump(\OpenTelemetry\Instrumentation\hook(null, 'never_defined_function', fn() => var_dump('PRE')));
var_dump('done');
?>
--EXPECT--
bool(true)
string(4) "done"
