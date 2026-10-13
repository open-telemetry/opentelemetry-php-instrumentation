--TEST--
Check WithSpan attribute arg referencing an undefined constant is skipped
--DESCRIPTION--
An unresolvable constant must not crash, and must not leak an exception into
the hooked function's frame: the arg is skipped and the call proceeds.

It is reported rather than skipped silently. Evaluation failure always leaves
an exception pending - there is no separate "quiet" failure mode to key off -
so suppressing it without a word would also hide an autoloader that threw
(attribute_args_throwing_autoload.phpt). The warning is what makes the two
distinguishable.
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

(new OpenTelemetry\Test\ConstArgs())->undefinedConst();
var_dump('after');
?>
--EXPECTF--
Warning: OpenTelemetry\Test\ConstArgs::undefinedConst(): OpenTelemetry: attribute arg evaluation threw exception, class=OpenTelemetry\Test\ConstArgs function=undefinedConst message=Undefined constant OpenTelemetry\API\Trace\SpanKind::DOES_NOT_EXIST in %s on line %d
string(3) "pre"
array(1) {
  ["name"]=>
  string(15) "undefined-const"
}
string(4) "post"
string(5) "after"
