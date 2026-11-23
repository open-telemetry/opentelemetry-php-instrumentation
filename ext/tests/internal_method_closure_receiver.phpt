--TEST--
Hooks receive the bound object or called class when calling an internal method through a closure
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

class HookDate extends DateTimeImmutable {}

hook(
    DateTimeImmutable::class,
    'createFromFormat',
    pre: static function ($receiver) use (&$pre): void {
        $pre = $receiver;
    },
    post: static function ($receiver) use (&$post): void {
        $post = $receiver;
    },
);

$calls = [
    'direct static call' => static fn () => HookDate::createFromFormat('Y', '2020'),
    'static array callable' => static fn () => call_user_func([HookDate::class, 'createFromFormat'], 'Y', '2020'),
    'static first-class callable' => static fn () => (HookDate::createFromFormat(...))('Y', '2020'),
    'static Closure::fromCallable' => static fn () => Closure::fromCallable([HookDate::class, 'createFromFormat'])('Y', '2020'),
];

foreach ($calls as $label => $call) {
    $pre = $post = null;
    printf("%s: %s\n", $label, $call()->format('Y'));
    var_dump($pre === HookDate::class, $post === HookDate::class);
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
direct static call: 2020
bool(true)
bool(true)
static array callable: 2020
bool(true)
bool(true)
static first-class callable: 2020
bool(true)
bool(true)
static Closure::fromCallable: 2020
bool(true)
bool(true)
