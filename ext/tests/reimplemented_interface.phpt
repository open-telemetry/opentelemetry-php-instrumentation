--TEST--
Check if hooks are invoked only once for reimplemented interfaces
--EXTENSIONS--
opentelemetry
--FILE--
<?php
interface A {
    function m(): void;
}
interface B extends A {
}
class C implements A, B {
    function m(): void {}
}

\OpenTelemetry\Instrumentation\hook(A::class, 'm', fn() => var_dump('PRE'), fn() => var_dump('POST'));

(new C)->m();
?>
--EXPECT--
string(3) "PRE"
string(4) "POST"
