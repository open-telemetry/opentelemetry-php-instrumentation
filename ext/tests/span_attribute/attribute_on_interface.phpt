--TEST--
Check if SpanAttribute can be applied to interface
--EXTENSIONS--
opentelemetry
--FILE--
<?php
namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\Instrumentation\WithSpan;
use OpenTelemetry\Instrumentation\SpanAttribute;

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
        #[SpanAttribute('renamed_one_from_interface')] string $one,
        string $two,
    ): void;
}

class TestClass implements TestInterface
{
    function foo(
        string $one,
        #[SpanAttribute('renamed_two_from_class')] string $two,
    ): void
    {
        var_dump('foo');
    }
}

(new TestClass())->foo('one', 'two');
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["renamed_one_from_interface"]=>
  string(3) "one"
  ["renamed_two_from_class"]=>
  string(3) "two"
}
string(3) "foo"
string(4) "post"