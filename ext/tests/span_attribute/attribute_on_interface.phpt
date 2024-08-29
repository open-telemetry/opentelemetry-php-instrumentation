--TEST--
Check if SpanAttribute can be applied to interface
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