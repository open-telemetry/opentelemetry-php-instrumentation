--TEST--
Check if custom attribute loaded
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
include dirname(__DIR__) . '/mocks/WithSpanHandler.php';
use OpenTelemetry\API\Instrumentation\WithSpan;

#[WithSpan]
function otel_attr_test(): void
{
  var_dump('test');
}

$reflection = new \ReflectionFunction('OpenTelemetry\API\Instrumentation\otel_attr_test');
var_dump($reflection->getAttributes()[0]->getName() == WithSpan::class);

otel_attr_test();
?>
--EXPECT--
bool(true)
string(3) "pre"
string(4) "test"
string(4) "post"