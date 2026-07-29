--TEST--
Check WithSpan attribute with name, span_kind, and attributes
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
opentelemetry.attr_pre_handler_function = custom_pre
opentelemetry.attr_post_handler_function = custom_post
--FILE--
<?php
include dirname(__DIR__) . '/mocks/WithSpan.php';
use OpenTelemetry\API\Instrumentation\WithSpan;

function custom_pre($obj, array $params, ?string $class, string $function, ?string $filename, ?int $lineno, array $attr_args, array $attributes): void
{
    var_dump('pre');
    var_dump($attr_args);
}

function custom_post(): void
{
    var_dump('post');
}

#[WithSpan('my-span', 1, attributes: ['key' => 'value'])]
function foo(): void
{
    var_dump('test');
}

foo();
?>
--EXPECT--
string(3) "pre"
array(2) {
  ["name"]=>
  string(7) "my-span"
  ["span_kind"]=>
  int(1)
}
string(4) "test"
string(4) "post"
