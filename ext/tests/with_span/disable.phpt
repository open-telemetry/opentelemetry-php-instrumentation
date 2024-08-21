--TEST--
Check if attribute hooks can be disabled by config
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = Off
--FILE--
<?php
namespace OpenTelemetry\API\Instrumentation;

use OpenTelemetry\Instrumentation\WithSpan;

class WithSpanHandler
{
    public static function pre(): void
    {
        var_dump('pre: should not be called');
    }
    public static function post(): void
    {
        var_dump('post: should not be called');
    }
}

#[WithSpan]
function otel_attr_test(): void
{
  var_dump('test');
}

otel_attr_test();
?>
--EXPECTF--
Warning: %s: OpenTelemetry: WithSpan attribute found but attribute hooks disabled in Unknown on line %d
string(4) "test"
