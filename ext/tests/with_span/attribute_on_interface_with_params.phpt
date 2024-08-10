--TEST--
Check if WithSpan can be applied to an interface with attribute args
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
        var_dump(func_get_args()[7])
    }
    public static function post(): void
    {
        var_dump('post');
    }
}

interface TestInterface
{
    #[WithSpan('one', 99, ['foo' => 'bar'])]
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
array(3) {
  ["name"]=>
  string(3) "one"
  ["span_kind"]=>
  int(99)
  ["attributes"]=>
  array(1) {
    ["foo"]=>
    string(3) "bar"
  }
}
string(3) "foo"
string(4) "post"