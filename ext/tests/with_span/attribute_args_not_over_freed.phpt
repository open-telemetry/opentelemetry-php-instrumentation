--TEST--
WithSpan attribute args are not over-freed across repeated observed calls
--DESCRIPTION--
Regression test for https://github.com/open-telemetry/opentelemetry-php/issues/2002
observer_begin() must not decrement the refcount of the values cached on the
op_array's #[WithSpan(...)] attribute. opcache is disabled so the literal stays a
refcounted (non-interned) zend_string, which is where the over-free bit.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
opcache.enable_cli = 0
--FILE--
<?php
namespace OpenTelemetry\API\Instrumentation;

include dirname(__DIR__) . '/mocks/WithSpan.php';
use OpenTelemetry\API\Instrumentation\WithSpan;

class WithSpanHandler
{
    public static function pre(): void {}
    public static function post(): void {}
}

class Demo
{
    #[WithSpan('demo.operation')]
    public function work(): void {}
}

$d = new Demo();
for ($i = 0; $i < 1000000; $i++) {
    $d->work();
}
echo "done\n";
?>
--EXPECT--
done
