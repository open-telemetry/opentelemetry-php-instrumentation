--TEST--
Check hook registered before class is defined still works
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook('LaterClass', 'hello', fn() => var_dump('PRE'), fn() => var_dump('POST'));

// Class defined after hook registration
class LaterClass {
    public static function hello(): void
    {
        var_dump('CALL');
    }
}

LaterClass::hello();
?>
--EXPECT--
string(3) "PRE"
string(4) "CALL"
string(4) "POST"
