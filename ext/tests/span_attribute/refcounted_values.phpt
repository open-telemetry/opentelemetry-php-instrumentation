--TEST--
Check that SpanAttribute keeps the caller's refcounted values valid
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
include dirname(__DIR__) . '/mocks/SpanAttribute.php';
include dirname(__DIR__) . '/mocks/WithSpanHandler.php';
use OpenTelemetry\API\Instrumentation\WithSpan;
use OpenTelemetry\API\Instrumentation\SpanAttribute;

#[WithSpan]
function foo(
    #[SpanAttribute] string $one,
    #[SpanAttribute] array $two
): void
{
}

// str_repeat() and range() build their results at run time, so both values are
// refcounted. A literal would be an interned string, and an interned string
// hides a missing reference count increment.
$one = str_repeat('a', 32);
$two = range(1, 4);

for ($i = 0; $i < 5; $i++) {
    foo($one, $two);
}

// Take the memory again. If the hook released the values too soon, the new
// allocations overwrite them.
$filler = [];
for ($i = 0; $i < 2000; $i++) {
    $filler[] = str_repeat('X', 32);
}

var_dump($one);
var_dump($two);
?>
--EXPECT--
string(3) "pre"
string(4) "post"
string(3) "pre"
string(4) "post"
string(3) "pre"
string(4) "post"
string(3) "pre"
string(4) "post"
string(3) "pre"
string(4) "post"
string(32) "aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa"
array(4) {
  [0]=>
  int(1)
  [1]=>
  int(2)
  [2]=>
  int(3)
  [3]=>
  int(4)
}
