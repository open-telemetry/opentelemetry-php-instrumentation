--TEST--
Check if span_kind can be passed via a const
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
include dirname(__DIR__) . '/mocks/WithSpanHandlerDumpAll.php';
include dirname(__DIR__) . '/mocks/SpanAttribute.php';
use OpenTelemetry\API\Instrumentation\WithSpan;
use OpenTelemetry\API\Instrumentation\SpanAttribute;

interface SpanKind
{
    public const KIND_PRODUCER = 3;
}

#[WithSpan(span_kind: SpanKind::KIND_PRODUCER)]
function foo(): void
{
    var_dump('foo');
}

foo();
?>
--EXPECTF--
string(3) "pre"
array(8) {
  [0]=>
  NULL
  [1]=>
  array(0) {
  }
  [2]=>
  NULL
  [3]=>
  string(37) "OpenTelemetry\API\Instrumentation\foo"
  [4]=>
  string(%d) "%s/tests/with_span/span_kind_const.php"
  [5]=>
  int(16)
  [6]=>
  array(1) {
    ["span_kind"]=>
    int(3)
  }
  [7]=>
  array(0) {
  }
}
string(3) "foo"
string(4) "post"