--TEST--
Check conflict detection with spaces as separators
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.conflicts=nonexistent Core
--FILE--
<?php
?>
--EXPECTF--
Notice: PHP Startup: Conflicting extension found (Core), OpenTelemetry extension will be disabled in %s
