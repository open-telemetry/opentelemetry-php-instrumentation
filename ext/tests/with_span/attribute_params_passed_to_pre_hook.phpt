--TEST--
Check if WithSpan parameters are passed to pre hook
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
        var_dump($args[7] ?? null);
    }
    public static function post(): void
    {
        var_dump('post');
    }
}

class Foo
{
    #[WithSpan('param1', 99, ['attr1' => 'value1', 'attr2' => 3.14])]
    function foo(): void
    {
        var_dump('foo');
    }
}

(new Foo())->foo();
?>
--EXPECT--
array(2) {
  ["name"]=>
  string(6) "param1"
  ["span_kind"]=>
  int(99)
}
array(2) {
  ["attr1"]=>
  string(6) "value1"
  ["attr2"]=>
  float(3.14)
}
string(3) "foo"
string(4) "post"