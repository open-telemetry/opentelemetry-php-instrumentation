--TEST--
Check hook with only pre callback (null post)
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn() => var_dump('PRE'), null);

function helloWorld() {
    var_dump('CALL');
}

helloWorld();
?>
--EXPECT--
string(3) "PRE"
string(4) "CALL"
