--TEST--
Check SpanAttribute name given as a class constant is evaluated
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
        'OpenTelemetry\\API\\Trace\\SpanKind' => '/mocks/SpanKind.php',
        'OpenTelemetry\\Test\\ConstArgs' => '/mocks/ConstArgs.php',
    ];
    if (isset($map[$class])) {
        include dirname(__DIR__) . $map[$class];
    }
});

(new OpenTelemetry\Test\ConstArgs())->spanAttributeName('value');
?>
--EXPECT--
string(3) "pre"
array(1) {
  ["custom.attribute.name"]=>
  string(5) "value"
}
string(4) "post"
