--TEST--
Check if function non-simple types can be passed as function params
--EXTENSIONS--
opentelemetry
--FILE--
<?php
namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\Instrumentation\WithSpan;
use OpenTelemetry\Instrumentation\SpanAttribute;

class WithSpanHandler
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

#[WithSpan]
function foo(
    #[SpanAttribute] array $one,
    #[SpanAttribute] object $two,
    #[SpanAttribute] callable $three,
    #[SpanAttribute] null $four,
): void
{
    var_dump('foo');
}

foo(
    ['foo' => 'bar'],
    new \stdClass(),
    function(){return 'fn';},
    null,
);
?>
--EXPECT--
string(3) "pre"
array(4) {
  ["one"]=>
  array(1) {
    ["foo"]=>
    string(3) "bar"
  }
  ["two"]=>
  object(stdClass)#1 (0) {
  }
  ["three"]=>
  object(Closure)#2 (0) {
  }
  ["four"]=>
  NULL
}
string(3) "foo"
string(4) "post"