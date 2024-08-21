--TEST--
Check if custom attribute can be applied to an interface
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

interface TestInterface
{
    #[WithSpan]
    public function sayFoo(): void;
}

interface OtherInterface
{
    #[WithSpan]
    public function sayBar(): void;
}

class TestClass implements TestInterface, OtherInterface
{
    public function sayFoo(): void
    {
        var_dump('foo');
    }
    public function sayBar(): void
    {
        var_dump('bar');
    }
}

$c = new TestClass();
$c->sayFoo();
$c->sayBar();
?>
--EXPECT--
string(3) "pre"
string(3) "foo"
string(4) "post"
string(3) "pre"
string(3) "bar"
string(4) "post"