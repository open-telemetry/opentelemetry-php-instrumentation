--TEST--
Check a transient attribute arg failure does not silence a later one
--DESCRIPTION--
The warn-once gate exists to stop a hot function flooding the log with a
failure that is a property of its declaration. Such a failure recurs on every
call, so suppressing the repeats loses nothing.

A failure that does *not* recur was transient, and the justification for
suppression does not apply to it. If the flag only ever latched, one transient
failure early in a request would silence a genuinely different fault for the
rest of it.

So the flag is cleared by a call that evaluates cleanly. Here the first call
fails and warns, two clean calls re-arm the gate, and a *fourth* call fails
again and must warn a second time. That second warning is the whole point: it
is the only assertion that distinguishes re-arming from latching.

The vehicle is `new` in the attribute arg, not an autoloader miss. An
autoloader can only fail once per class name - after it defines the class, no
later call can be made to fail - so a declaration whose failure recurs on
demand needs a constructor that re-runs. Verified: the ctor is evaluated once
per call (three calls, three evaluations), so phase 3 is reachable.

Checked against a latch-only build (`if (failed) attr_eval_warned = true;`):
that mutant drops the second warning and fails this test. An earlier version of
this test used an autoloader and no fourth call, and passed against that same
mutant - it asserted nothing. Do not simplify it back.

Companion to attribute_args_failure_warns_once.phpt, which covers the
suppression half and explains why both use methods rather than plain
functions.
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

class Phase
{
    public static bool $broken = true;
}

// Re-evaluated on every call, so the same declaration can fail, succeed, then
// fail again - which an autoloader-based vehicle cannot do.
class Maker
{
    public function __construct()
    {
        if (Phase::$broken) {
            throw new \RuntimeException('the fault');
        }
    }
}

class Subject
{
    #[WithSpan('n', new Maker())]
    public function m(): void
    {
    }
}

$s = new Subject();
echo "--- call 1: fails, warns ---\n";
$s->m();
echo "--- calls 2-3: clean, re-arm the gate ---\n";
Phase::$broken = false;
$s->m();
$s->m();
echo "--- call 4: fails again, must warn a second time ---\n";
Phase::$broken = true;
$s->m();
var_dump('after');
?>
--EXPECTF--
--- call 1: fails, warns ---

Warning: Subject::m(): OpenTelemetry: attribute arg evaluation threw exception, class=Subject function=m message=the fault in %s
string(3) "pre"
string(4) "post"
--- calls 2-3: clean, re-arm the gate ---
string(3) "pre"
string(4) "post"
string(3) "pre"
string(4) "post"
--- call 4: fails again, must warn a second time ---

Warning: Subject::m(): OpenTelemetry: attribute arg evaluation threw exception, class=Subject function=m message=the fault in %s
string(3) "pre"
string(4) "post"
string(5) "after"
