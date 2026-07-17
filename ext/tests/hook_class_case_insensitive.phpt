--TEST--
Check class hooks are matched case-insensitively
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook('DEMO', 'HELLO', fn() => var_dump('PRE'), fn() => var_dump('POST'));

class Demo {
    public static function hello(): void
    {
        var_dump('CALL');
    }
}

Demo::hello();
?>
--EXPECT--
string(3) "PRE"
string(4) "CALL"
string(4) "POST"
