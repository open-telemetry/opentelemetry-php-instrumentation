--TEST--
Check if pre hook can expand params of function with extra parameters not provided by call site
--EXTENSIONS--
opentelemetry
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    pre: function($instance, array $params) {
        return [$params[0], 'b', 'c', 'd'];
    },
    post: fn() => null
);

function helloWorld($a, $b) {
    var_dump(func_get_args());
}
helloWorld('a');
?>
--EXPECTF--
Notice: helloWorld(): OpenTelemetry: pre hook invalid argument index 2, class=null function=helloWorld in %s
array(2) {
  [0]=>
  string(1) "a"
  [1]=>
  string(1) "b"
}
