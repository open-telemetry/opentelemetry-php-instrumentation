--TEST--
Check if exceptions thrown in hooks work if custom error handler throws
--DESCRIPTION--
If the extension internally logs errors/warnings in a way that set_error_handler gets invoked, then any
exceptions/errors may cause the process to crash or hang if raising a throwable was not safe at that moment.
--EXTENSIONS--
opentelemetry
--FILE--
<?php
set_error_handler(function (int $errno, string $message) {
    throw new \Error('Throw from error handler: ' . $message);
});
function helloWorld() {
    throw new \Error('test');
}
\OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    pre: static function () : void {
        throw new \Exception('pre');
    }
);
helloWorld();
?>
--EXPECTF--
Warning: helloWorld(): OpenTelemetry: pre hook threw exception, class=null function=helloWorld message=pre in %s

Fatal error: Uncaught Error: test in %s
Stack trace:
#0 %s: helloWorld()
#1 {main}
  thrown in %s