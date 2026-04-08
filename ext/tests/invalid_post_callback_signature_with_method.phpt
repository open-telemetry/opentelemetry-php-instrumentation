--TEST--
Test invalid post callback signature for class method
--EXTENSIONS--
opentelemetry
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    'TestClass',
    'test',
    null,
    static function (array $params) {
        //missing param 1 (object), incorrect type for param 1
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
string(4) "test"

Warning: TestClass::test(): OpenTelemetry: post hook invalid signature, class=TestClass function=test in %s on line %d
