--TEST--
Check exit() from a duplicate attribute exception destructor stops evaluation
--DESCRIPTION--
Sibling of attribute_args_duplicate_exception_destructor.phpt. That one covers a
duplicate exception whose destructor *throws*, which the drain absorbs. This one
covers the case the drain deliberately does not absorb: a destructor that calls
exit().

An unwind exit is left pending so the isolation window can report it - nothing
here can honour an exit() - but the caller must then stop evaluating. The drain
therefore returns a flag and the caller sets eval_state->stopped, the same state
an arg's own exit() produces.

Without that signal the unwind exit stays pending while the loop continues, and
the next arg is evaluated with EG(exception) set - evaluation carrying on past
an exit() that cannot be honoured. This test is the version of that reached
through exit() rather than throw.

Note the harm here is the continued evaluation itself, not an engine abort.
On 8.3/8.4 debug builds a constant expression that emits a diagnostic while
succeeding can trip ZEND_FETCH_CLASS_CONSTANT's ZEND_ASSERT(!EG(exception)),
but that was measured *not* to be reachable this way: given both its
conditions on an attribute arg, 8.4.1 debug evaluates cleanly and reports a
warning, while the same conditions in userland code abort. Attribute args
evaluate via zend_ast_evaluate(), not the VM handler that holds the assertion.

The assertion is the autoloader, which must NOT run: it stands for "an arg was
evaluated after the exit". Arg 3 is an autoloaded class constant so that it is
a real runtime evaluation rather than a const-folded literal - a literal there
makes this test pass either way.

Verified on 8.3.10 with forced clean rebuilds: with the signal the autoloader
does not run; with the `state->stopped = true` removed it does.
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

class Exiter extends \RuntimeException
{
    public function __destruct()
    {
        exit(3);
    }
}

class Maker
{
    public function __construct(public int $n)
    {
        throw new Exiter("fault {$this->n}");
    }
}

// Must not run: it would mean an arg was evaluated after the exit().
spl_autoload_register(function (string $class): void {
    if ($class === 'Survivor') {
        echo "FAIL: evaluated an arg after exit()\n";
        eval('class Survivor { const V = "yes"; }');
    }
});

class Subject
{
    #[WithSpan(new Maker(1), new Maker(2), attributes: ['survivor' => \Survivor::V])]
    public function m(): void
    {
    }
}

(new Subject())->m();
var_dump('after');
?>
--EXPECTF--
Warning: Subject::m(): OpenTelemetry: exit() during attribute arg evaluation cannot be honoured, class=Subject function=m in %s

Warning: Subject::m(): OpenTelemetry: attribute arg evaluation threw exception, class=Subject function=m message=fault 1 in %s
string(3) "pre"
array(0) {
}
string(4) "post"
string(5) "after"
