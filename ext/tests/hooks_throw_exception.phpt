--TEST--
Check if exceptions thrown in hooks are isolated and logged
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', pre: fn() => throw new Exception('thrown in pre'), post: fn() => throw new Exception('thrown in post'));

function helloWorld() {
    var_dump('function');
    throw new Exception('original');
}

try {
    helloWorld();
} catch (Exception $e) {
    var_dump($e->getMessage());
    var_dump($e->getPrevious());
}
?>
--EXPECTF--

Warning: helloWorld(): OpenTelemetry: pre hook threw exception, class=null function=helloWorld message=thrown in pre in %s
string(8) "function"

Warning: helloWorld(): OpenTelemetry: post hook threw exception, class=null function=helloWorld message=thrown in post in %s
string(8) "original"
NULL