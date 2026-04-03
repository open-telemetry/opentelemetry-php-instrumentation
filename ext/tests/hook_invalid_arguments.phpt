--TEST--
Check hook() with invalid argument types
--EXTENSIONS--
opentelemetry
--FILE--
<?php
// Missing function_name (too few args)
try {
    \OpenTelemetry\Instrumentation\hook(null);
} catch (ArgumentCountError $e) {
    var_dump('too few args caught');
}

// Non-closure for pre hook
try {
    \OpenTelemetry\Instrumentation\hook(null, 'test', 'not_a_closure');
} catch (TypeError $e) {
    var_dump('type error caught');
}
?>
--EXPECT--
string(19) "too few args caught"
string(17) "type error caught"
