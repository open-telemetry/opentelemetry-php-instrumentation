--TEST--
Check SpanAttribute params survive an exception left by a successful arg evaluation
--DESCRIPTION--
zend_get_attribute_value() returns SUCCESS without clearing EG(exception), and
constant-AST evaluation runs userland along paths that then succeed. A userland
error handler that converts diagnostics to exceptions can therefore leave an
exception pending with no FAILURE to key off.

Nothing may treat "an exception is pending" as proof that evaluation failed. An
earlier version keyed the per-parameter #[SpanAttribute] loop on
EG(exception) == NULL, so one converted diagnostic silently skipped *every*
parameter - and reported nothing, because no failure had been recorded.

The vehicle is the destructor of a const-expr temporary. A ternary releases its
condition after evaluating the chosen branch and does not check EG(exception)
afterwards, so a deprecation raised from Noisy::__destruct leaves an exception
pending while the arg evaluates successfully. Both the WithSpan args and the
#[SpanAttribute] param must still arrive, and the converted exception must be
reported rather than swallowed.

Converting diagnostics to exceptions is standard in Symfony, Laravel and
PHPUnit, so this is an ordinary configuration, not a corner case.

Diagnostics raised by the engine itself are a poor vehicle: php-src keeps adding
EG(exception) checks after them, which turns SUCCESS into FAILURE and drops the
arg before the loop sees it. A lossy float array key was used here until
GH-22895 added such a check to array_set_zval_key() in 8.4.27, 8.5.12 and 8.6.
If this vehicle stops working too, find another rather than capping the test.
Do NOT "fix" a failure by keying the loop on EG(exception) again; that is the
exact bug this test was written to catch, and it skipped every parameter
silently.
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
use OpenTelemetry\API\Instrumentation\SpanAttribute;

class Noisy
{
    public function __destruct()
    {
        trigger_error('Noisy released', E_USER_DEPRECATED);
    }
}

set_error_handler(function (int $no, string $msg) {
    throw new \ErrorException($msg, 0, $no);
});

class Subject
{
    #[WithSpan(attributes: ['k' => new Noisy() ? 'from-attributes' : 'unreachable'], name: 'my-span')]
    public function m(#[SpanAttribute('param.key')] string $p): void
    {
    }
}

(new Subject())->m('V');
var_dump('after');
?>
--EXPECTF--
Warning: Subject::m(): OpenTelemetry: attribute arg evaluation threw exception, class=Subject function=m message=Noisy released in %s on line %d
string(3) "pre"
array(2) {
  ["k"]=>
  string(15) "from-attributes"
  ["param.key"]=>
  string(1) "V"
}
string(4) "post"
string(5) "after"
