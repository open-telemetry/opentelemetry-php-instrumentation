--TEST--
Check if hooking a method takes priority over WithSpan
----DESCRIPTION--
Attribute-based hooks are only applied if no other hooks are registered on a function or method.
--EXTENSIONS--
opentelemetry
--FILE--
<?php
namespace OpenTelemetry\Instrumentation;

use OpenTelemetry\Instrumentation\WithSpan;

class Handler
{
    public static function pre(): void
    {
        var_dump('should not be called');
    }
    public static function post(): void
    {
        var_dump('should not be called');
    }
}

class Foo
{
    #[WithSpan()]
    function foo(): void
    {
        var_dump('foo');
    }
}

\OpenTelemetry\Instrumentation\hook(Foo::class, 'foo', function(){var_dump('pre');}, function(){var_dump('post');});

(new Foo())->foo();
?>
--EXPECT--
string(3) "pre"
string(3) "foo"
string(4) "post"