--TEST--
Check hook with only post callback (null pre)
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', null, fn() => var_dump('POST'));

function helloWorld() {
    var_dump('CALL');
}

helloWorld();
?>
--EXPECT--
string(4) "CALL"
string(4) "POST"
