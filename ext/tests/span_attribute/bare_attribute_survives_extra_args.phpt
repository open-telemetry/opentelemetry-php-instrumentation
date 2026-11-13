--TEST--
Check bare SpanAttribute keys on the parameter name with extra args
--DESCRIPTION--
A bare #[SpanAttribute] keys on the parameter name. With extra args the
declared parameter goes through func_get_args()'s first loop, and the key must
still be found there.
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
    public function m(#[SpanAttribute] string $a): void
    {
        var_dump('body');
    }
}

// Four more args than declared, none of which can carry an attribute.
// The bare attribute keys on the parameter name, so 'a' is the only entry.
(new Subject())->m('A', 'B', 'C', 'D', 'E');
?>
--EXPECT--
string(3) "pre"
array(1) {
  ["a"]=>
  string(1) "A"
}
string(4) "body"
string(4) "post"
