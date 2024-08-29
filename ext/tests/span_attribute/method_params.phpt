--TEST--
Check if method params can be passed via SpanAttribute
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
--FILE--
<?php
namespace OpenTelemetry\API\Instrumentation;

include dirname(__DIR__) . '/mocks/WithSpan.php';
include dirname(__DIR__) . '/mocks/WithSpanHandlerDumpAttributes.php';
include dirname(__DIR__) . '/mocks/SpanAttribute.php';
use OpenTelemetry\API\Instrumentation\WithSpan;
use OpenTelemetry\API\Instrumentation\SpanAttribute;

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