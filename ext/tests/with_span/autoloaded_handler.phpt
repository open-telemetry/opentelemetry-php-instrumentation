--TEST--
Check if WithSpanHandler can be provided by an autoloader
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
--FILE--
<?php
include dirname(__DIR__) . '/mocks/WithSpan.php';

use OpenTelemetry\API\Instrumentation\WithSpan;
use OpenTelemetry\API\Instrumentation\WithSpanHandler;

function my_autoloader($class) {
    var_dump("autoloading: " . $class);
    $file = dirname(__DIR__) . '/mocks/WithSpanHandler.php';
    if (file_exists($file)) {
        include $file;
    } else {
        die("could not autoload: ".$class);
    }
}
spl_autoload_register('my_autoloader');

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
--EXPECT--
string(62) "autoloading: OpenTelemetry\API\Instrumentation\WithSpanHandler"
string(3) "pre"
string(3) "foo"
string(4) "post"