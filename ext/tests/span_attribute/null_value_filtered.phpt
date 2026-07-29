--TEST--
Check SpanAttribute with null value is filtered out
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
function foo(#[SpanAttribute] ?string $name = null): void
{
    var_dump('test');
}

foo(null);
?>
--EXPECT--
string(3) "pre"
array(0) {
}
string(4) "test"
string(4) "post"
