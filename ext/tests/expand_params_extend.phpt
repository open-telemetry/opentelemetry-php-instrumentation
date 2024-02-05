--TEST--
Check if pre hook can expand params of function when that requires extending the stack
--EXTENSIONS--
opentelemetry
--INI--
opentelemetry.allow_stack_extension=On
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
array(8) {
  [0]=>
  string(1) "a"
  [1]=>
  string(1) "b"
  [2]=>
  string(1) "c"
  [3]=>
  string(1) "d"
  [4]=>
  string(1) "e"
  [5]=>
  string(1) "f"
  [6]=>
  string(1) "g"
  [7]=>
  string(1) "h"
}
