--TEST--
Check if attributes from SpanAttribute replace attributes with same name from WithSpan
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