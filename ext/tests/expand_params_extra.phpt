--TEST--
Check if pre hook can expand params of function with extra parameters not provided by call site
--DESCRIPTION--
Extra parameters for user functions are parameters that were provided at call site but were not present in the function
declaration. The extension only supports modifying existing ones, not adding new ones. Test that a warning is logged if
adding new ones is attempted and that it does not crash.
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
Warning: helloWorld(): OpenTelemetry: pre hook invalid argument index 2, class=null function=helloWorld in %s
array(2) {
  [0]=>
  string(1) "a"
  [1]=>
  string(1) "b"
}
