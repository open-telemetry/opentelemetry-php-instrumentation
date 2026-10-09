--TEST--
Check a SpanAttribute whose name cannot be evaluated is dropped, not renamed
--DESCRIPTION--
A #[SpanAttribute] name arg can be an unevaluated IS_CONSTANT_AST, so producing
the key can fail - an unresolvable constant from an optional dependency that is
not installed, an autoloader that throws.

The key used to fall back to the parameter name. That is the one failure mode on
these paths that produces a *wrong* result rather than a missing one: the value
is correct and present, under a key the author never asked for. Downstream
queries "db.user.id" and finds nothing, while "id" sits there looking like
deliberate configuration. A gap is diagnosable; a rename is not.

So the attribute is dropped when its declared name cannot be produced. Here
db.user.id is unresolvable and db.table is a plain literal: the second must
still arrive, which is also what keeps this distinct from "the whole pass was
abandoned".

A *bare* #[SpanAttribute] has no name arg at all and is defined to key on the
parameter name - that is not a fallback and is unaffected. The third parameter
below covers it.

Methods, not plain functions: see KNOWN_ISSUES.md #8.
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
use OpenTelemetry\API\Instrumentation\SpanAttribute;
use OpenTelemetry\API\Instrumentation\WithSpan;

// Never resolves, so the first name arg cannot be evaluated. Autoloaded rather
// than merely absent so the arg stays an IS_CONSTANT_AST at runtime instead of
// being const-folded at compile time.
spl_autoload_register(function (string $class): void {
});

class Repo
{
    #[WithSpan('Repo::find')]
    public function find(
        #[SpanAttribute(\OpenTelemetry\Test\Keys::USER_ID)] int $id,
        #[SpanAttribute('db.table')] string $table,
        #[SpanAttribute] string $mode,
    ): void {
    }
}

(new Repo())->find(7, 'users', 'ro');
var_dump('after');
?>
--EXPECTF--
Warning: Repo::find(): OpenTelemetry: attribute arg evaluation threw exception, class=Repo function=find message=Class "OpenTelemetry\Test\Keys" not found in %s on line %d
string(3) "pre"
array(2) {
  ["db.table"]=>
  string(5) "users"
  ["mode"]=>
  string(2) "ro"
}
string(4) "post"
string(5) "after"
