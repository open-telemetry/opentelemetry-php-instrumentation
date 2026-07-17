--TEST--
Check hooks are matched case-insensitively
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'HelloWorld', fn() => var_dump('PRE'), fn() => var_dump('POST'));

function helloworld() {
    var_dump('CALL');
}

helloworld();
?>
--EXPECT--
string(3) "PRE"
string(4) "CALL"
string(4) "POST"
