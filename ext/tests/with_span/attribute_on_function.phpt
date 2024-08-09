--TEST--
Check if custom attribute loaded
--EXTENSIONS--
opentelemetry
--FILE--
<?php
namespace OpenTelemetry\Instrumentation;

use OpenTelemetry\Instrumentation\WithSpan;

class Handler
{
    public static function pre(): void
    {
        var_dump('pre');
    }
    public static function post(): void
    {
        var_dump('post');
    }
}

#[WithSpan]
function otel_attr_test(): void
{
  var_dump('test');
}

$reflection = new \ReflectionFunction('OpenTelemetry\Instrumentation\otel_attr_test');
var_dump($reflection->getAttributes()[0]->getName() == WithSpan::class);

otel_attr_test();
?>
--EXPECT--
bool(true)
string(3) "pre"
string(4) "test"
string(4) "post"