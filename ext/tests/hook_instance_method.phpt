--TEST--
Check hooking instance method provides object as 1st param
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    Demo::class,
    'hello',
    function($obj) {
        var_dump(get_class($obj));
    },
    function($obj) {
        var_dump(get_class($obj));
    }
);

class Demo {
    public function hello(): void
    {
        var_dump('hello');
    }
}

$d = new Demo();
$d->hello();
?>
--EXPECT--
string(4) "Demo"
string(5) "hello"
string(4) "Demo"
