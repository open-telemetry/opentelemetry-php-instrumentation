--TEST--
Check if method params can be passed via SpanAttribute
--EXTENSIONS--
opentelemetry
--FILE--
<?php
namespace OpenTelemetry\Instrumentation;

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
    #[WithSpan]
    function foo(
        #[SpanAttribute('renamed_one')] string $one,
        #[SpanAttribute] int $two,
        #[SpanAttribute('renamed_three')] float $three,
        #[SpanAttribute] bool $four,
        string $five,
        #[SpanAttribute] string $six
    ): void
    {
        var_dump('foo');
    }
}

$c = new TestClass();
$c->foo('one', 99, 3.14159, true, 'five', 'six');
?>
--EXPECT--
string(3) "pre"
array(5) {
  ["renamed_one"]=>
  string(3) "one"
  ["two"]=>
  int(99)
  ["renamed_three"]=>
  float(3.14159)
  ["four"]=>
  bool(true)
  ["six"]=>
  string(3) "six"
}
string(3) "foo"
string(4) "post"