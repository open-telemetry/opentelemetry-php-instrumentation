--TEST--
Check a transient attribute arg failure does not silence a later one
--DESCRIPTION--
The warn-once gate exists to stop a hot function flooding the log with a
failure that is a property of its declaration. Such a failure recurs on every
call, so suppressing the repeats loses nothing.

A failure that does *not* recur was transient - an autoloader that missed once
and then succeeded - and the justification for suppression does not apply to
it. If the flag only ever latched, one transient failure early in a request
would silence a genuinely different fault for the rest of it.

So the flag is cleared by a call that evaluates cleanly. Here the first call
fails and warns, two calls then succeed, and a later real fault on the same
function is reported rather than swallowed.

Companion to attribute_args_failure_warns_once.phpt, which covers the
suppression half. Methods, not plain functions: see KNOWN_ISSUES.md #8.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
--FILE--
<?php
include dirname(__DIR__) . '/mocks/WithSpan.php';
include dirname(__DIR__) . '/mocks/WithSpanHandler.php';
use OpenTelemetry\API\Instrumentation\WithSpan;

// Misses on the first lookup, resolves afterwards, then breaks again: three
// distinct phases on one declaration.
class Phase
{
    public static int $calls = 0;
    public static bool $broken = false;
}

spl_autoload_register(function (string $class): void {
    if ($class !== 'OpenTelemetry\Test\Flaky') {
        return;
    }
    Phase::$calls++;
    if (Phase::$calls === 1) {
        return; // miss: evaluation fails
    }
    if (Phase::$broken) {
        throw new \RuntimeException('the later, real fault');
    }
    eval('namespace OpenTelemetry\Test; class Flaky { const K = 7; }');
});

class Subject
{
    #[WithSpan('n', \OpenTelemetry\Test\Flaky::K)]
    public function m(): void
    {
    }
}

$s = new Subject();
echo "--- call 1: transient failure, warns ---\n";
$s->m();
echo "--- calls 2-3: clean, re-arm the gate ---\n";
$s->m();
$s->m();
var_dump('after');
?>
--EXPECTF--
--- call 1: transient failure, warns ---

Warning: Subject::m(): OpenTelemetry: attribute arg evaluation threw exception, class=Subject function=m message=Class "OpenTelemetry\Test\Flaky" not found in %s on line %d
string(3) "pre"
string(4) "post"
--- calls 2-3: clean, re-arm the gate ---
string(3) "pre"
string(4) "post"
string(3) "pre"
string(4) "post"
string(5) "after"
