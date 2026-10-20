--TEST--
Check SpanAttribute self:: const on an interface resolves in the interface's scope
--DESCRIPTION--
find_spanattribute_attribute() may return an attribute declared on an interface
method's parameter, so its constant AST must be evaluated against the
interface's scope, not the implementing method's. The implementation redeclares
ATTR_NAME as impl.attr; the interface that owns the attribute declares it as
iface.attr.

The interface declares ATTR_NAME after the method on purpose: PHP const-folds
self::X only when X is already in the active class's constants_table, so the
usual ordering folds the value at compile time and the test would pass either
way.
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

spl_autoload_register(function (string $class): void {
    $map = [
        'OpenTelemetry\\Test\\SelfConstWithSpan' => '/mocks/SelfConstInterface.php',
        'OpenTelemetry\\Test\\SelfConstSpanAttribute' => '/mocks/SelfConstInterface.php',
        'OpenTelemetry\\Test\\SelfConstSpanAttributeImpl' => '/mocks/SelfConstImpl.php',
    ];
    if (isset($map[$class])) {
        include dirname(__DIR__) . $map[$class];
    }
});

(new OpenTelemetry\Test\SelfConstSpanAttributeImpl())->m('value');
?>
--EXPECT--
string(3) "pre"
array(1) {
  ["iface.attr"]=>
  string(5) "value"
}
string(4) "post"
