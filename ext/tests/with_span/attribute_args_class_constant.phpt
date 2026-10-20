--TEST--
Check WithSpan attribute args referencing a class constant are evaluated
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
include dirname(__DIR__) . '/mocks/WithSpanHandlerDumpAttributeArgs.php';

// SpanKind and ConstArgs are autoloaded on first use, so that SpanKind is not
// in the class table when ConstArgs is compiled. The attribute args are then
// stored as unevaluated IS_CONSTANT_AST zvals.
spl_autoload_register(function (string $class): void {
    $map = [
        'OpenTelemetry\\API\\Trace\\SpanKind' => '/mocks/SpanKind.php',
        'OpenTelemetry\\Test\\ConstArgs' => '/mocks/ConstArgs.php',
    ];
    if (isset($map[$class])) {
        include dirname(__DIR__) . $map[$class];
    }
});

$c = new OpenTelemetry\Test\ConstArgs();
$c->positional();
$c->named();
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["name"]=>
  string(10) "positional"
  ["span_kind"]=>
  int(3)
}
string(4) "post"
string(3) "pre"
array(2) {
  ["name"]=>
  string(5) "named"
  ["span_kind"]=>
  int(3)
}
string(4) "post"
