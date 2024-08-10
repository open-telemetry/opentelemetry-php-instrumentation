--TEST--
Check if SpanAttribute can be applied to interface
--EXTENSIONS--
opentelemetry
--XFAIL--
Not implemented
--FILE--
<?php
namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\Instrumentation\WithSpan;

class Handler
{
    public static function pre(): void
    {
        var_dump('pre');
        var_dump(func_get_args()[7]);
    }
    public static function post(): void
    {
        var_dump('post');
    }
}

interface TestInterface
{
    #[WithSpan]
    function foo(
        #[SpanAttribute('renamed')] string $one,
    ): void;
}

class TestClass implements TestInterface
{
    function foo(string $one): void
    {
        var_dump('foo');
    }
}

(new TestClass())->foo('one');
?>
--EXPECT--
string(3) "pre"
array(1) {
  ["renamed"]=>
  string(3) "one"
}
string(3) "foo"
string(4) "post"