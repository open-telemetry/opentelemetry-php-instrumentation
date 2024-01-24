--TEST--
Check if pre hook can expand params of function if they are part of function definition
--EXTENSIONS--
opentelemetry
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    pre: function($instance, array $params) {
        return [$params[0], 'b'];
    },
    post: fn() => null
);

function helloWorld($a, $b) {
    var_dump(func_get_args());
}
helloWorld('a');
?>
--EXPECT--
array(2) {
  [0]=>
  string(1) "a"
  [1]=>
  string(1) "b"
}
