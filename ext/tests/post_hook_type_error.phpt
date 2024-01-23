--TEST--
Check if type error in post hook is handled
--EXTENSIONS--
opentelemetry
--FILE--
<?php
class Foo
{
  public function bar(): void
  {
    var_dump('bar');
  }
}

\OpenTelemetry\Instrumentation\hook(
    Foo::class,
    'bar',
    fn() => var_dump('pre'),
    fn(string $scope, array $params, mixed $returnValue, ?Throwable $throwable) => var_dump('post')); //NB invalid type for $scope

(new Foo())->bar();
var_dump('baz');
--EXPECTF--
string(3) "pre"
string(3) "bar"

Warning: Foo::bar(): OpenTelemetry: post hook threw exception, class=Foo function=bar message=%sArgument #1 ($scope) must be of type string, Foo given%s
string(3) "baz"