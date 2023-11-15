--TEST--
Calling die/exit still executes post hooks
--EXTENSIONS--
opentelemetry
--FILE--

<?php

use function OpenTelemetry\Instrumentation\hook;

function goodbye() {
    var_dump('goodbye');
    die;
}

\OpenTelemetry\Instrumentation\hook(null, 'goodbye', fn() => var_dump('PRE'), fn() => var_dump('POST'));

goodbye();
?>

--EXPECT--
string(3) "PRE"
string(7) "goodbye"
string(4) "POST"