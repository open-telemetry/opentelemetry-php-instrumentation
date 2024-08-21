--TEST--
Check if WithSpan can be applied to a method
--EXTENSIONS--
opentelemetry
--FILE--
<?php
namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\Instrumentation\WithSpan;

class WithSpanHandler
{
    public static function pre(): void
    {
        var_dump('pre');
    }
    public static function post(): void
    {
        var_dump('post');
    }
}

class TestClass
{
    #[WithSpan]
    function sayFoo(): void
    {
        var_dump('foo');
    }
}

$reflection = new \ReflectionMethod(TestClass::class, 'sayFoo');
var_dump($reflection->getAttributes()[0]->getName() == WithSpan::class);

$c = new TestClass();
$c->sayFoo();
?>
--EXPECT--
bool(true)
string(3) "pre"
string(3) "foo"
string(4) "post"