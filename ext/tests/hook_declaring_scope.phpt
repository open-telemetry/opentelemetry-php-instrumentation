--TEST--
Check declaring scope is provided for class method hooks
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    'ParentClass',
    'hello',
    function($obj, array $params, ?string $class, string $function) {
        var_dump('declaring_scope: ' . $class);
    }
);

class ParentClass {
    public function hello(): void {}
}

class ChildClass extends ParentClass {}

$c = new ChildClass();
$c->hello();
?>
--EXPECT--
string(28) "declaring_scope: ParentClass"
