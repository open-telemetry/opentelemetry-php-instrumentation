--TEST--
Check WithSpan attribute arg referencing an undefined constant is skipped
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
        'OpenTelemetry\\API\\Trace\\SpanKind' => '/mocks/SpanKind.php',
        'OpenTelemetry\\Test\\ConstArgs' => '/mocks/ConstArgs.php',
    ];
    if (isset($map[$class])) {
        include dirname(__DIR__) . $map[$class];
    }
});

// An unresolvable constant must not crash, and must not leak an exception
// into the hooked function's frame: the arg is skipped and the call proceeds.
(new OpenTelemetry\Test\ConstArgs())->undefinedConst();
var_dump('after');
?>
--EXPECT--
string(3) "pre"
array(1) {
  ["name"]=>
  string(15) "undefined-const"
}
string(4) "post"
string(5) "after"
