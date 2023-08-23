--TEST--
Check if opentelemetry extension is loaded but disabled with a conflicting extension
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.conflicts=Core
--FILE--
<?php
?>
--EXPECTF--
Notice: PHP Startup: Conflicting extension found (Core), OpenTelemetry extension will be disabled in %s
