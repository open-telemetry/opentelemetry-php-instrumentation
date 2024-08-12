--TEST--
Check if attributes from SpanAttribute replace attributes with same name from WithSpan
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

class TestClass
{
    #[WithSpan(attributes: ['one' => 'one_from_withspan', 'two' => 'two_from_withspan'])]
    function foo(
        #[SpanAttribute] string $one,
    ): void
    {
        var_dump('foo');
    }
}

$c = new TestClass();
$c->foo('one');
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["two"]=>
  string(17) "two_from_withspan"
  ["one"]=>
  string(3) "one"
}
string(3) "foo"
string(4) "post"