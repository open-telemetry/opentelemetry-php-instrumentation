--TEST--
Check hook registered before function is defined still works
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'laterFunction', fn() => var_dump('PRE'), fn() => var_dump('POST'));

// Function defined after hook registration
function laterFunction() {
    var_dump('CALL');
}

laterFunction();
?>
--EXPECT--
string(3) "PRE"
string(4) "CALL"
string(4) "POST"
