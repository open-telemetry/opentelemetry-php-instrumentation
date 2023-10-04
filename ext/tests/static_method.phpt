--TEST--
Check hooking static class methods provides class name as 1st param
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    Demo::class,
    'hello',
    function($class) {
        var_dump($class);
    },
    function($class) {
        var_dump($class);
    }
);

class Demo {
    public static function hello(): void
    {
        var_dump('hello');
    }
}

Demo::hello();
?>
--EXPECT--
string(4) "Demo"
string(5) "hello"
string(4) "Demo"
