--TEST--
Check a repeating attribute arg failure warns once per function
--DESCRIPTION--
Attribute args are re-evaluated on every observed call, so a failure that is a
property of the declaration recurs on every call. Reporting each one would turn
a single misconfigured constant on a hot function into an unbounded warning
stream, in a form the user cannot filter: E_CORE_WARNING does not reach
set_error_handler() and "@" does not suppress it.

So the warning is gated on the observer, which is per function. Two calls to
the same function warn once; a second function warns separately, because its
declaration is a different fault to report.

The args themselves must keep being delivered on every call - only the warning
is suppressed, not the behaviour.

Uses methods rather than plain functions deliberately: a #[WithSpan] on a
top-level function is dropped once the file is served from the opcache
(KNOWN_ISSUES.md #8, pre-existing), which would make this test fail for an
unrelated reason under opcache or JIT. Methods are unaffected, so this test
holds in every configuration.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
--FILE--
<?php
include dirname(__DIR__) . '/mocks/WithSpan.php';
include dirname(__DIR__) . '/mocks/SpanAttribute.php';
include dirname(__DIR__) . '/mocks/WithSpanHandlerDumpAttributes.php';
use OpenTelemetry\API\Instrumentation\WithSpan;

// Never resolvable, so every evaluation of the span_kind arg fails.
spl_autoload_register(function (string $class): void {
});

class Subject
{
    #[WithSpan('first', \OpenTelemetry\API\Trace\SpanKind::KIND_CLIENT, ['fn' => 'first'])]
    public function first(): void
    {
    }

    #[WithSpan('second', \OpenTelemetry\API\Trace\SpanKind::KIND_CLIENT, ['fn' => 'second'])]
    public function second(): void
    {
    }
}

$s = new Subject();
echo "--- first, three times ---\n";
$s->first();
$s->first();
$s->first();
echo "--- second, twice ---\n";
$s->second();
$s->second();
var_dump('after');
?>
--EXPECTF--
--- first, three times ---

Warning: Subject::first(): OpenTelemetry: attribute arg evaluation threw exception, class=Subject function=first message=Class "OpenTelemetry\API\Trace\SpanKind" not found in %s on line %d
string(3) "pre"
array(1) {
  ["fn"]=>
  string(5) "first"
}
string(4) "post"
string(3) "pre"
array(1) {
  ["fn"]=>
  string(5) "first"
}
string(4) "post"
string(3) "pre"
array(1) {
  ["fn"]=>
  string(5) "first"
}
string(4) "post"
--- second, twice ---

Warning: Subject::second(): OpenTelemetry: attribute arg evaluation threw exception, class=Subject function=second message=Class "OpenTelemetry\API\Trace\SpanKind" not found in %s on line %d
string(3) "pre"
array(1) {
  ["fn"]=>
  string(6) "second"
}
string(4) "post"
string(3) "pre"
array(1) {
  ["fn"]=>
  string(6) "second"
}
string(4) "post"
string(5) "after"
