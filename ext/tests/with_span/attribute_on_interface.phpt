--TEST--
Check if custom attribute can be applied to an interface
--XFAIL--
Not implemented
--EXTENSIONS--
opentelemetry
--FILE--
<?php
namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\Instrumentation\WithSpan;

class Handler
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
    function sayFoo(): void;
}

class TestClass implements TestInterface
{
    function sayFoo(): void
    {
        var_dump('foo');
    }
}

(new TestClass())->sayFoo();
?>
--EXPECT--
string(3) "pre"
string(3) "foo"
string(4) "post"