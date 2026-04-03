--TEST--
Check hooks work with deep class hierarchy (3+ levels)
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    'GrandParent',
    'hello',
    function($obj) {
        var_dump('pre: ' . get_class($obj));
    },
    function($obj) {
        var_dump('post: ' . get_class($obj));
    }
);

class GrandParent {
    public function hello(): void
    {
        var_dump('hello');
    }
}

class ParentClass extends GrandParent {}

class ChildClass extends ParentClass {}

$c = new ChildClass();
$c->hello();
?>
--EXPECT--
string(15) "pre: ChildClass"
string(5) "hello"
string(16) "post: ChildClass"
