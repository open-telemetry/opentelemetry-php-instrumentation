--TEST--
Check SpanAttribute params survive an exception left by a successful arg evaluation
--DESCRIPTION--
zend_get_attribute_value() returns SUCCESS without clearing EG(exception), and
constant-AST evaluation emits diagnostics along paths that then succeed. A
userland error handler that converts diagnostics to exceptions therefore leaves
an exception pending with no FAILURE to key off.

Nothing may treat "an exception is pending" as proof that evaluation failed. An
earlier version keyed the per-parameter #[SpanAttribute] loop on
EG(exception) == NULL, so one converted diagnostic silently skipped *every*
parameter - and reported nothing, because no failure had been recorded.

Here the lossy float array key 1.5 emits E_DEPRECATED while evaluating
successfully. Both the WithSpan args and the #[SpanAttribute] param must still
arrive, and the converted exception must be reported rather than swallowed.

Converting diagnostics to exceptions is standard in Symfony, Laravel and
PHPUnit, so this is an ordinary configuration, not a corner case.

Capped at < 8.6 because the vehicle no longer produces the shape being tested,
not because the invariant stopped mattering. PHP 8.6 added an EG(exception)
check to array_set_zval_key() (Zend/zend_API.c), so a lossy float key now
returns FAILURE instead of SUCCESS-with-pending-exception, and the arg is
dropped before the loop sees it - expected output would be array(1), not
array(2). 8.6 is right to do this - the same check fixes an engine assertion
that 8.3/8.4 abort on - and once FAILURE is returned the value is gone, because
zend_get_attribute_value() frees it before returning. Keeping the arg would
mean suppressing the application's error handler around attribute evaluation,
which is possible but a semantic change, deliberately not made here.

If a vehicle is found that still yields SUCCESS with a pending exception on
8.6, prefer restoring the cap - the invariant applies to every version. Probed
and rejected on 8.6: lossy float key, null key, non-numeric arithmetic. Do NOT
"fix" this by keying the loop on EG(exception) again; that is the exact bug
this test was written to catch, and it skipped every parameter silently.
--SKIPIF--
<?php
if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1');
if (PHP_VERSION_ID >= 80600) die('skip vehicle returns FAILURE on PHP >= 8.6, see DESCRIPTION');
?>
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

// Autoloaded, so the const stays an unevaluated IS_CONSTANT_AST at runtime.
spl_autoload_register(function (string $class): void {
    if ($class === 'OpenTelemetry\Test\LossyKey') {
        eval('namespace OpenTelemetry\Test; class LossyKey { const F = 1.5; }');
    }
});

set_error_handler(function (int $no, string $msg) {
    throw new \ErrorException($msg, 0, $no);
});

class Subject
{
    #[WithSpan(attributes: [\OpenTelemetry\Test\LossyKey::F => 'from-attributes'], name: 'my-span')]
    public function m(#[SpanAttribute('param.key')] string $p): void
    {
    }
}

(new Subject())->m('V');
var_dump('after');
?>
--EXPECTF--
Warning: Subject::m(): OpenTelemetry: attribute arg evaluation threw exception, class=Subject function=m message=Implicit conversion from float 1.5 to int loses precision in %s on line %d
string(3) "pre"
array(2) {
  [1]=>
  string(15) "from-attributes"
  ["param.key"]=>
  string(1) "V"
}
string(4) "post"
string(5) "after"
