--TEST--
Check a throwing __destruct on a suppressed attribute arg exception is contained
--DESCRIPTION--
A suppressed exception has to be released, and releasing an exception object
runs its userland __destruct, which can itself throw.

The release used to happen after the isolation window had closed and EG() had
been restored, so that new exception landed on unisolated state. The VM enters
the observed function straight after a begin handler without checking for a
pending exception, so the result was a fatal error attributed to the observed
method - a frame that never threw it - and a 255 exit status, from nothing but
observing a function.

The release now has its own isolation window. Anything the destructor raises is
discarded: it ran only because this code was cleaning up, so there is no frame
it can fairly be attributed to.

The chain is three deep, because each discarded exception's destructor throws
another, and absorbing only the first would leave the second escaping exactly
as before. A self-throwing destructor (one that throws its own class forever)
exhausts the stack inside a single release - that is inherent to PHP and
happens with no extension loaded, so it is not what this guards.

Methods, not plain functions: see KNOWN_ISSUES.md #8.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
--FILE--
<?php
include dirname(__DIR__) . '/mocks/WithSpan.php';
include dirname(__DIR__) . '/mocks/WithSpanHandler.php';
use OpenTelemetry\API\Instrumentation\WithSpan;

class Link extends \LogicException
{
    public int $n;

    public function __construct(int $n)
    {
        $this->n = $n;
        parent::__construct("link$n");
    }

    public function __destruct()
    {
        echo "destruct link{$this->n}\n";
        if ($this->n > 1) {
            throw new Link($this->n - 1);
        }
    }
}

spl_autoload_register(function (string $class): void {
    if ($class === 'OpenTelemetry\Test\Missing') {
        throw new Link(3);
    }
});

class Subject
{
    #[WithSpan('n', \OpenTelemetry\Test\Missing::K)]
    public function m(): void
    {
        echo "body\n";
    }
}

(new Subject())->m();
var_dump('after');
?>
--EXPECTF--
Warning: Subject::m(): OpenTelemetry: attribute arg evaluation threw exception, class=Subject function=m message=link3 in %s on line %d
destruct link3
destruct link2
destruct link1
string(3) "pre"
body
string(4) "post"
string(5) "after"
