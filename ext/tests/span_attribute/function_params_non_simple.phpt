--TEST--
Check if function non-simple types can be passed as function params
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
include dirname(__DIR__) . '/mocks/SpanAttribute.php';
include dirname(__DIR__) . '/mocks/WithSpanHandlerDumpAttributes.php';
use OpenTelemetry\API\Instrumentation\WithSpan;
use OpenTelemetry\API\Instrumentation\SpanAttribute;

#[WithSpan]
function foo(
    #[SpanAttribute] array $one,
    #[SpanAttribute] object $two,
    #[SpanAttribute] callable $three,
    #[SpanAttribute] $four,
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