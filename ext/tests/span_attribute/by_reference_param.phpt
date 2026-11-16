--TEST--
Check SpanAttribute applies to by-reference parameters
--DESCRIPTION--
A by-reference parameter's slot holds an IS_REFERENCE, not the value. It has to
be dereferenced before the value type is checked, or the attribute is dropped.
Extra args are passed so the declared parameter goes through the first arg loop
in func_get_args() and the variadic through the second.
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
        #[SpanAttribute('key_s')] string &$s,
        #[SpanAttribute] string &...$more,
    ): void {
        $s = 'changed';
    }
}

$s = 'S';
$m1 = 'M1';
$m2 = 'M2';
(new Subject())->m($s, $m1, $m2);
var_dump($s);
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["key_s"]=>
  string(1) "S"
  ["more"]=>
  string(2) "M1"
}
string(4) "post"
string(7) "changed"
