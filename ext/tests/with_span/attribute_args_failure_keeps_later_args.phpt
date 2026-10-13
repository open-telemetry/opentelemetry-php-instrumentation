--TEST--
Check one unresolvable attribute arg does not discard the args after it
--DESCRIPTION--
Evaluation of an IS_CONSTANT_AST arg can fail (here an unresolvable class
constant from an optional dependency that is not installed). That failure must
be confined to its own arg.

The args after it are typically plain literals with no AST and no possible
failure mode - the attributes array below, and both #[SpanAttribute] names -
so losing them would silently drop telemetry the user did supply, on both
evaluation paths at once (func_get_attribute_args() and func_get_args()).

Regression test: an earlier version of the fix stopped at the first failure and
left the exception pending, which also disabled the whole #[SpanAttribute] pass
for every parameter. Everything below except span_kind must survive.
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

// SpanKind is never resolvable, so the span_kind arg cannot be evaluated.
spl_autoload_register(function (string $class): void {
});

class Repo
{
    #[WithSpan('Repo::find', \OpenTelemetry\API\Trace\SpanKind::KIND_CLIENT, ['db.system' => 'mysql'])]
    public function find(
        #[SpanAttribute('db.user.id')] int $id,
        #[SpanAttribute('db.table')] string $table,
    ): void {
    }
}

(new Repo())->find(7, 'users');
var_dump('after');
?>
--EXPECTF--
Warning: Repo::find(): OpenTelemetry: attribute arg evaluation threw exception, class=Repo function=find message=Class "OpenTelemetry\API\Trace\SpanKind" not found in %s on line %d
string(3) "pre"
array(3) {
  ["db.system"]=>
  string(5) "mysql"
  ["db.user.id"]=>
  int(7)
  ["db.table"]=>
  string(5) "users"
}
string(4) "post"
string(5) "after"
