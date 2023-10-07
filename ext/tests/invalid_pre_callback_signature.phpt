--TEST--
Test invalid pre callback signature
--DESCRIPTION--
The invalid callback signature should not cause a fatal, so it is checked before execution. If the function signature
is invalid, the callback will not be called and a message will be written to error_log.
--EXTENSIONS--
opentelemetry
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    'TestClass',
    'test',
    static function (array $params, string $class, string $function, ?string $filename, ?int $lineno) {
        //missing param 1 (object)
        var_dump('pre');
    },
    static function () {
        var_dump('post');
    }
);

class TestClass {
    public static function test(): void
    {
        var_dump('test');
    }
}

TestClass::test();
?>
--EXPECTF--
Warning: TestClass::test(): OpenTelemetry: pre hook invalid signature, class=TestClass function=test in %s on line %d
string(4) "test"
string(4) "post"
