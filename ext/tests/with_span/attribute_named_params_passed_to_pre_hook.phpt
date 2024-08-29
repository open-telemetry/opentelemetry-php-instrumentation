--TEST--
Check if named attribute parameters are passed to pre hook
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

class WithSpanHandler
{
    public static function pre(): void
    {
        $args = func_get_args();
        var_dump($args[6] ?? null);
    }
    public static function post(): void
    {
        var_dump('post');
    }
}

class Foo
{
    #[WithSpan(span_kind: 3)]
    function foo(): void
    {
        var_dump('foo');
    }
}

(new Foo())->foo();
?>
--EXPECT--
array(1) {
  ["span_kind"]=>
  int(3)
}
string(3) "foo"
string(4) "post"