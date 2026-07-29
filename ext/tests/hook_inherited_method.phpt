--TEST--
Check hooking parent class method fires for child class
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    'ParentClass',
    'hello',
    function($obj, array $params, ?string $class, string $function) {
        var_dump($class);
    },
    function($obj, array $params, mixed $ret, ?Throwable $e, ?string $class, string $function) {
        var_dump($class);
    }
);

class ParentClass {
    public function hello(): void
    {
        var_dump('hello');
    }
}

class ChildClass extends ParentClass {}

$c = new ChildClass();
$c->hello();
?>
--EXPECT--
string(11) "ParentClass"
string(5) "hello"
string(11) "ParentClass"
