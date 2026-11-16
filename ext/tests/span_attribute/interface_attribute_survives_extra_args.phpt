--TEST--
Check SpanAttribute inherited from an interface applies when extra args are passed
--DESCRIPTION--
With more args than declared, func_get_args() consumes the declared parameters
in its first loop. The attribute here is on the interface, not the
implementation, so the first loop has to fall back to the interface too.
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

interface I
{
    #[WithSpan]
    function m(
        #[SpanAttribute('a')] string $x,
        #[SpanAttribute] string $y,
    ): void;
}

class C implements I
{
    function m(string $x, string $y): void
    {
        var_dump('body');
    }
}

(new C())->m('X', 'Y', 'extra');
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["a"]=>
  string(1) "X"
  ["y"]=>
  string(1) "Y"
}
string(4) "body"
string(4) "post"
