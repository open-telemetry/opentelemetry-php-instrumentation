--TEST--
Check multiple hooks execute in correct order (pre: FIFO, post: LIFO)
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'hello',
    fn() => var_dump('pre-1'),
    fn() => var_dump('post-1')
);
\OpenTelemetry\Instrumentation\hook(null, 'hello',
    fn() => var_dump('pre-2'),
    fn() => var_dump('post-2')
);
\OpenTelemetry\Instrumentation\hook(null, 'hello',
    fn() => var_dump('pre-3'),
    fn() => var_dump('post-3')
);

function hello() {
    var_dump('CALL');
}

hello();
?>
--EXPECT--
string(5) "pre-1"
string(5) "pre-2"
string(5) "pre-3"
string(4) "CALL"
string(6) "post-3"
string(6) "post-2"
string(6) "post-1"
