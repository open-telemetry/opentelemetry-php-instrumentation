--TEST--
Invalid callback is ignored
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
opentelemetry.attr_pre_handler_function = "Invalid::pre"
opentelemetry.attr_post_handler_function = "Also\Invalid::post"
--FILE--
<?php
use OpenTelemetry\Instrumentation\WithSpan;

#[WithSpan]
function foo(): void
{
  var_dump('test');
}

var_dump(ini_get('opentelemetry.attr_pre_handler_function'));
var_dump(ini_get('opentelemetry.attr_post_handler_function'));
foo();
?>
--EXPECT--
string(12) "Invalid::pre"
string(18) "Also\Invalid::post"
string(4) "test"