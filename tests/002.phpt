--TEST--
Check if hook returns true
--EXTENSIONS--
otel_instrumentation
--FILE--
<?php
$ret = \OpenTelemetry\Instrumentation\hook(null, 'some_function');

var_dump($ret);
?>
--EXPECT--
bool(true)
