--TEST--
Check inherited SpanAttribute uses the parameter index, not the interface index
--DESCRIPTION--
find_spanattribute_attribute() must look up the parameter index, not the
interface's position in ce->interfaces. With one parameter and one interface
the two are both 0 and the bug does not show, so this uses four parameters and
two interfaces.
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

interface Marker {}

interface Subject
{
    #[WithSpan]
    function m(
        #[SpanAttribute('k0')] string $a,
        #[SpanAttribute('k1')] string $b,
        #[SpanAttribute('k2')] string $c,
        #[SpanAttribute('k3')] string $d,
    ): void;
}

// Marker first, so Subject is interfaces[1]. The implementation carries no
// attributes of its own: every key below has to come from the interface.
class Impl implements Marker, Subject
{
    function m(string $a, string $b, string $c, string $d): void
    {
        var_dump('body');
    }
}

(new Impl())->m('0', '1', '2', '3');
?>
--EXPECT--
string(3) "pre"
array(4) {
  ["k0"]=>
  string(1) "0"
  ["k1"]=>
  string(1) "1"
  ["k2"]=>
  string(1) "2"
  ["k3"]=>
  string(1) "3"
}
string(4) "body"
string(4) "post"
