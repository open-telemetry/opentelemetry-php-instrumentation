--TEST--
Check if opentelemetry disable ignores bad input
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.conflicts=,
--FILE--
<?php
var_dump(extension_loaded('opentelemetry'));
?>
--EXPECTF--
bool(true)
