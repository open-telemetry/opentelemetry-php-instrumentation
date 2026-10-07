--TEST--
Check WithSpan class constants nested in the attributes array are evaluated
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

(new OpenTelemetry\Test\ConstArgs())->inAttributes();
?>
--EXPECT--
string(3) "pre"
array(1) {
  ["key"]=>
  int(3)
}
string(4) "post"
