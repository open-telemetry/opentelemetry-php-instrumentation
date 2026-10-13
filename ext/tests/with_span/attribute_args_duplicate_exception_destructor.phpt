--TEST--
Check a throwing destructor on a duplicate attribute exception does not eat later args
--DESCRIPTION--
Companion to attribute_args_throwing_exception_destructor.phpt, and easy to
confuse with it. That one covers the *reported* exception, released after the
isolation window closes, where a throwing destructor used to become a fatal
attributed to the observed method. This one covers a *duplicate* exception,
released mid-evaluation while later args are still to come - a different site,
a different failure, and it is silent rather than fatal.

Only the first exception from attribute arg evaluation is kept, to be reported;
later ones are released as they arrive. Releasing an exception object runs its
userland __destruct, which can itself throw - and that throw lands while the
remaining args are still being evaluated.

Left pending, it is indistinguishable from an exception raised by the next arg's
own evaluation. On PHP >= 8.6 it is worse than cosmetic: a pending exception
makes the next zend_get_attribute_value() return FAILURE, and that arg is
dropped - 8.6 fails that call outright when EG(exception) is set on entry,
where 8.5 and earlier evaluated regardless. So the release drains what the
destructor raises instead of leaving it for the next arg to be blamed for.

Here args 1 and 2 both throw, so arg 2's exception is the discarded one, and its
destructor throws in turn. Arg 3 - the attributes array - is evaluated after
that and must still arrive. Exactly one warning, naming the *first* fault, since
the destructor's exception has no frame to attribute it to.

Two details are load-bearing; both were got wrong first time, and both make the
test pass against a build with the drain removed:

- **Arg 3 must still be unevaluated at call time.** A literal
  `['survivor' => 'yes']` is const-folded at compile time, so nothing is
  evaluated after the destructor throws and there is no arg left to poison.
  `Survivor::V` is autoloaded to keep it an IS_CONSTANT_AST: PHP only
  const-folds a class constant already present in the class table, so lazy
  loading forces a real runtime evaluation.
- **Two args must fail.** The first exception is kept for reporting, so only a
  second failure reaches the release path being tested.

On 8.1-8.5 this test passes with or without the drain, because a pending
exception there does not fail the next evaluation - so a green 8.3 run says
nothing. Verified on 8.6.0RC3 with forced clean rebuilds: with the drain,
array(1) and the autoloader runs; with `attr_eval_release_discarded()` cut down
to a bare OBJ_RELEASE, array(0) and the autoloader never runs.
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

class Nasty extends \RuntimeException
{
    public function __destruct()
    {
        throw new \LogicException('from destructor');
    }
}

class Maker
{
    public function __construct(public int $n)
    {
        throw new Nasty("fault {$this->n}");
    }
}

// Autoloaded, so the arg after the throwing destructor is still an
// unevaluated IS_CONSTANT_AST and is really evaluated at call time.
spl_autoload_register(function (string $class): void {
    if ($class === 'Survivor') {
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
Warning: Subject::m(): OpenTelemetry: attribute arg evaluation threw exception, class=Subject function=m message=fault 1 in %s
string(3) "pre"
array(1) {
  ["survivor"]=>
  string(3) "yes"
}
string(4) "post"
string(5) "after"
