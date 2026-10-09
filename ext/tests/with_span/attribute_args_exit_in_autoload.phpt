--TEST--
Check exit() during WithSpan attribute arg evaluation is reported, not silent
--DESCRIPTION--
Evaluating an IS_CONSTANT_AST attribute arg can run userland code: resolving
SomeClass::CONST triggers the autoloader. If that userland calls exit()/die(),
PHP represents it as an unwind exit in EG(exception).

That unwind exit cannot be honoured from here. ZEND_EXIT throws it and jumps to
the handler in the frame that ran exit(), and the VM then runs begin handlers
immediately before entering the observed function without checking for a
pending exception - so leaving one set is not a state it accepts. (The same is
true of a plain hook() pre-hook that calls exit(); that is a separate,
pre-existing limitation.)

What this test pins down is that the extension does not resume silently: it
warns that the exit() could not be honoured, then proceeds. Previously the
unwind exit was cleared with no trace at all.
--SKIPIF--
<?php if (PHP_VERSION_ID < 80100) die('skip requires PHP >= 8.1'); ?>
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.attr_hooks_enabled = On
--FILE--
<?php
include dirname(__DIR__) . '/mocks/WithSpan.php';
include dirname(__DIR__) . '/mocks/WithSpanHandlerDumpAttributeArgs.php';
use OpenTelemetry\API\Instrumentation\WithSpan;

spl_autoload_register(function (string $class): void {
    if ($class === 'OpenTelemetry\\Test\\ExitKind') {
        echo "autoload: calling exit\n";
        exit;
    }
});

// ExitKind is not in the class table at compile time, so this arg cannot be
// const-folded and is evaluated at run time, from inside the begin handler.
#[WithSpan('my-span', \OpenTelemetry\Test\ExitKind::KIND)]
function foo(): void
{
    var_dump('foo body');
}

foo();
var_dump('after');
?>
--EXPECTF--
autoload: calling exit

Warning: foo(): OpenTelemetry: exit() during attribute arg evaluation cannot be honoured, class=null function=foo in %s on line %d
string(3) "pre"
array(1) {
  ["name"]=>
  string(7) "my-span"
}
string(8) "foo body"
string(4) "post"
string(5) "after"
