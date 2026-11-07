--TEST--
Check interface SpanAttributes reach a variadic implementation
--DESCRIPTION--
A variadic method can implement an interface method with more parameters. The
interface's attributes then sit at indexes the implementation never declared,
so the lookup must not be bounded by the implementation's num_args.

A bare attribute at such an index has no parameter name to key on, so it is
dropped rather than given an invented key.
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
        #[SpanAttribute('b')] string $y,
        #[SpanAttribute] string $z,
    ): void;
}

class C implements I
{
    function m(string ...$args): void
    {
        var_dump('body');
    }
}

(new C())->m('X', 'Y', 'Z');
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["a"]=>
  string(1) "X"
  ["b"]=>
  string(1) "Y"
}
string(4) "body"
string(4) "post"
