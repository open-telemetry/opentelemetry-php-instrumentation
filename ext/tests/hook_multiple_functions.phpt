--TEST--
Check hooks on multiple different functions work independently
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'funcA', fn() => var_dump('pre-A'));
\OpenTelemetry\Instrumentation\hook(null, 'funcB', fn() => var_dump('pre-B'));

function funcA() { var_dump('A'); }
function funcB() { var_dump('B'); }

funcA();
funcB();
funcA();
?>
--EXPECT--
string(5) "pre-A"
string(1) "A"
string(5) "pre-B"
string(1) "B"
string(5) "pre-A"
string(1) "A"
