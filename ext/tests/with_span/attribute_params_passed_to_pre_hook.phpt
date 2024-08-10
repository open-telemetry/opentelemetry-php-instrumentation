--TEST--
Check if WithSpan parameters are passed to pre hook
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
        $args = func_get_args();
        var_dump($args[6] ?? null);
    }
    public static function post(): void
    {
        var_dump('post');
    }
}

class Foo
{
    #[WithSpan('param1', 99, ['attr1' => 'value1', 'attr2' => 3.14])]
    function foo(): void
    {
        var_dump('foo');
    }
}

(new Foo())->foo();
?>
--EXPECT--
array(3) {
  ["name"]=>
  string(6) "param1"
  ["span_kind"]=>
  int(99)
  ["attributes"]=>
  array(2) {
    ["attr1"]=>
    string(6) "value1"
    ["attr2"]=>
    float(3.14)
  }
}
string(3) "foo"
string(4) "post"