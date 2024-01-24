--TEST--
Check if process exits gracefully after using ini_set with an opentelemetry option
--EXTENSIONS--
opentelemetry
--FILE--
<?php
ini_set('opentelemetry.conflicts', 'test');
var_dump('done');
?>
--EXPECT--
string(4) "done"
