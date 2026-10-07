--TEST--
Check extra positional WithSpan args do not read past the known arg names
--DESCRIPTION--
with_span_attribute_args_keys[] holds only "name" and "span_kind", but was
indexed by every positional arg up to attr->argc, so a 4th positional arg read
past the end of the array and produced a garbage key. WithSpan declares no such
parameter, but the extension reads the attribute without instantiating it, so
this is reachable.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
--FILE--
<?php
include dirname(__DIR__) . '/mocks/WithSpan.php';
include dirname(__DIR__) . '/mocks/WithSpanHandlerDumpAttributeArgs.php';
use OpenTelemetry\API\Instrumentation\WithSpan;

#[WithSpan('my-span', 1, ['key' => 'value'], 'extra-four', 'extra-five')]
function foo(): void
{
    var_dump('foo');
}

foo();
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["name"]=>
  string(7) "my-span"
  ["span_kind"]=>
  int(1)
}
string(3) "foo"
string(4) "post"
