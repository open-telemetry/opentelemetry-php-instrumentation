--TEST--
Check that expanding params without allow_stack_extension produces warning
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.allow_stack_extension=Off
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    pre: function($instance, array $params) {
        return [$params[0], 'b', 'c', 'd', 'e', 'f', 'g', 'h'];
    },
    post: fn() => null
);

function helloWorld($a, $b) {
    var_dump(func_get_args());
}
helloWorld('a');
?>
--EXPECTF--
Warning: helloWorld(): OpenTelemetry: pre hook invalid argument index %d - stack extension must be enabled with opentelemetry.allow_stack_extension option, class=null function=helloWorld in %s on line %d
array(%d) {
  [0]=>
  string(1) "a"
  [1]=>
  string(1) "b"
}
