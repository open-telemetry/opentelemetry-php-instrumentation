--TEST--
Hooks receive the bound object when calling an internal method through a closure
--EXTENSIONS--
opentelemetry
--SKIPIF--
<?php
if (PHP_VERSION_ID < 80200) {
    die('skip Internal function observers require PHP 8.2 or newer');
}
?>
--FILE--
<?php

use function OpenTelemetry\Instrumentation\hook;

$collection = new ArrayObject([1, 2, 3]);
$pre = $post = null;

hook(
    ArrayObject::class,
    'count',
    pre: static function ($receiver) use (&$pre): void {
        $pre = $receiver;
    },
    post: static function ($receiver) use (&$post): void {
        $post = $receiver;
    },
);

$calls = [
    'direct call' => static fn () => $collection->count(),
    'array callable' => static fn () => call_user_func([$collection, 'count']),
    'first-class callable' => static fn () => ($collection->count(...))(),
    'Closure::fromCallable' => static fn () => Closure::fromCallable([$collection, 'count'])(),
];

foreach ($calls as $label => $call) {
    $pre = $post = null;
    printf("%s: %d\n", $label, $call());
    var_dump($pre === $collection, $post === $collection);
}
?>
--EXPECT--
direct call: 3
bool(true)
bool(true)
array callable: 3
bool(true)
bool(true)
first-class callable: 3
bool(true)
bool(true)
Closure::fromCallable: 3
bool(true)
bool(true)
