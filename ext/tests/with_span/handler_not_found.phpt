--TEST--
Check if warning emitted when default handler does not exist
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
--FILE--
<?php
namespace OpenTelemetry\API\Instrumentation;

include dirname(__DIR__) . '/mocks/WithSpan.php';
use OpenTelemetry\API\Instrumentation\WithSpan;

class TestClass
{
    #[WithSpan]
    function sayFoo(): void
    {
        var_dump('foo');
    }
}

$c = new TestClass();
$c->sayFoo();
?>
--EXPECTF--

Warning: OpenTelemetry\API\Instrumentation\TestClass::sayFoo(): Failed to initialize pre hook callable in %s
string(3) "foo"

Warning: OpenTelemetry\API\Instrumentation\TestClass::sayFoo(): Failed to initialize post hook callable in %s