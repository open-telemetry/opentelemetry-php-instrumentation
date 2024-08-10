--TEST--
Check if WithSpan handlers can be changed via config
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_pre_handler_function = custom_pre
opentelemetry.attr_post_handler_function = custom_post
--FILE--
<?php
use OpenTelemetry\Instrumentation\WithSpan;

function custom_pre(): void
{
    var_dump('custom_pre handler');
}

function custom_post(): void
{
    var_dump('custom_post handler');
}

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
string(10) "custom_pre"
string(11) "custom_post"
string(18) "custom_pre handler"
string(4) "test"
string(19) "custom_post handler"