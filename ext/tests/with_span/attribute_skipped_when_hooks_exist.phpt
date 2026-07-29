--TEST--
Check WithSpan attribute hooks are skipped when manual hooks already exist
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
opentelemetry.attr_pre_handler_function = default_pre
opentelemetry.attr_post_handler_function = default_post
--FILE--
<?php
include dirname(__DIR__) . '/mocks/WithSpan.php';
use OpenTelemetry\API\Instrumentation\WithSpan;

function default_pre(): void
{
    var_dump('default_pre: should not be called');
}

function default_post(): void
{
    var_dump('default_post: should not be called');
}

// Register manual hooks before the WithSpan-attributed function is called
\OpenTelemetry\Instrumentation\hook(null, 'foo',
    fn() => var_dump('manual_pre'),
    fn() => var_dump('manual_post')
);

#[WithSpan]
function foo(): void
{
    var_dump('test');
}

foo();
?>
--EXPECT--
string(10) "manual_pre"
string(4) "test"
string(11) "manual_post"
