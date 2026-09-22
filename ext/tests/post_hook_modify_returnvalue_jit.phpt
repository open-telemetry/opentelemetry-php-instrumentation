--TEST--
Regression: post-hook return-value replacement works with JIT
--DESCRIPTION--
The observer return hook code was writing to `execute_data->return_value`
instead of `retval`. Under the interpreter these point to the same memory, but
under the JIT they do not always match.

This test enables the JIT and observes a function that returns an object. The
post hook replaces that object with another one. A scalar return also exposes
the incorrect result, but an object exposes the memory-safety failure to tools
such as AddressSanitizer and Valgrind. This failure is not specific to tracing
JIT; other JIT modes would fail too.
--EXTENSIONS--
opentelemetry
opcache
--INI--
opcache.enable_cli=1
opcache.jit=tracing
opcache.jit_buffer_size=64M
opcache.jit_hot_loop=1
opcache.jit_hot_func=1
opcache.jit_hot_return=1
opcache.jit_hot_side_exit=1
--FILE--
<?php

final class Box
{
    public function __construct(public int $value) {}
}

function makeBox(): Box
{
    return new Box(1);
}

OpenTelemetry\Instrumentation\hook(
    null,
    'makeBox',
    post: static fn (mixed $object, array $params, Box $return): Box => new Box(2),
);

$sum = 0;
for ($i = 0; $i < 10000; ++$i) {
    $sum += makeBox()->value;
}

// Correct hooks return 2, so 2 * 10,000 = 20,000.
var_dump($sum);

?>
--EXPECT--
int(20000)
