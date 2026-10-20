--TEST--
Check SpanAttribute on a variadic parameter is still found
--DESCRIPTION--
op_array.num_args excludes the variadic, so a bound of `i < num_args` on the
parameter name drops an attribute declared on the variadic itself. Both keys
are asserted: key_a comes from the first arg loop and key_rest from the second.
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

// A method, not a plain function. See survives_extra_args.phpt.
class Subject
{
    #[WithSpan]
    public function m(
        #[SpanAttribute('key_a')] string $a,
        #[SpanAttribute('key_rest')] string ...$rest,
    ): void {
        var_dump('body');
    }
}

// The variadic collects 'B' and 'C'. It is one declared parameter with one
// attribute, so it yields a single key; 'B' is the value at its own index.
(new Subject())->m('A', 'B', 'C');
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["key_a"]=>
  string(1) "A"
  ["key_rest"]=>
  string(1) "B"
}
string(4) "body"
string(4) "post"
