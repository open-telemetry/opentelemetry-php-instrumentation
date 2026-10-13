--TEST--
Check WithSpan self:: const on an interface resolves in the interface's scope
--DESCRIPTION--
find_withspan_attribute() may return an attribute declared on an interface
method, so its constant AST must be evaluated against the interface's scope, not
the implementing method's. The implementation redeclares KIND as 99; the
interface that owns the attribute declares it as 1.

The interface declares KIND after the method on purpose: PHP const-folds self::X
only when X is already in the active class's constants_table, so the usual
ordering folds the value at compile time and the test would pass either way.
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

spl_autoload_register(function (string $class): void {
    $map = [
        'OpenTelemetry\\Test\\SelfConstWithSpan' => '/mocks/SelfConstInterface.php',
        'OpenTelemetry\\Test\\SelfConstSpanAttribute' => '/mocks/SelfConstInterface.php',
        'OpenTelemetry\\Test\\SelfConstWithSpanImpl' => '/mocks/SelfConstImpl.php',
    ];
    if (isset($map[$class])) {
        include dirname(__DIR__) . $map[$class];
    }
});

(new OpenTelemetry\Test\SelfConstWithSpanImpl())->go();
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["name"]=>
  string(14) "iface-withspan"
  ["span_kind"]=>
  int(1)
}
string(4) "post"
