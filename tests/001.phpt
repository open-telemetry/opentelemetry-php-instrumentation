--TEST--
Check if otel_instrumentation is loaded
--EXTENSIONS--
otel_instrumentation
--FILE--
<?php
echo 'The extension "otel_instrumentation" is available';
?>
--EXPECT--
The extension "otel_instrumentation" is available
