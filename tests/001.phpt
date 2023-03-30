--TEST--
Check if otel_instrumentation is loaded
--EXTENSIONS--
otel_instrumentation
--FILE--
<?php
printf('The extension "otel_instrumentation" is available, version %s', phpversion('otel_instrumentation'));
?>
--EXPECTF--
The extension "otel_instrumentation" is available, version %s
