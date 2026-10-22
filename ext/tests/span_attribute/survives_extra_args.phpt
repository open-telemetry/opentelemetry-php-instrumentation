--TEST--
Check SpanAttribute still applies when more args are passed than declared
--DESCRIPTION--
With more args than declared, func_get_args() consumes the declared parameters
in its first loop. The attribute check must run there too. Without extra args
only one loop runs, so the first call is the control.
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

// A method, because opcache drops #[WithSpan] on a top-level function. That
// would fail this test for an unrelated reason under an opcache run.
class Subject
{
    #[WithSpan]
    public function m(
        #[SpanAttribute('declared_a')] string $a,
        string ...$rest,
    ): void {
        var_dump('body');
    }
}

$s = new Subject();
echo "-- no extra args --\n";
$s->m('A');
echo "-- with extra args --\n";
$s->m('A', 'B', 'C');
?>
--EXPECT--
-- no extra args --
string(3) "pre"
array(1) {
  ["declared_a"]=>
  string(1) "A"
}
string(4) "body"
string(4) "post"
-- with extra args --
string(3) "pre"
array(1) {
  ["declared_a"]=>
  string(1) "A"
}
string(4) "body"
string(4) "post"
