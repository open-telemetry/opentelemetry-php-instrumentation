--TEST--
Check if pre hook can modify extra parameters
--DESCRIPTION--
Extra parameters for user functions are parameters that were provided at call site but were not present in the function
declaration. It is important to test how these are handled because they are stored differently in memory and it should
be checked that the extension handles them correctly.
--EXTENSIONS--
opentelemetry
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'helloWorld',
    pre: function($instance, array $params) {
        return ['b0', 'b1', 'b2', 'b3', 'b4', 'b5', 'b6'];
    },
    post: fn() => null
);

function helloWorld($a, $b) {
    var_dump(func_get_args());
}
helloWorld('a0', 'a1', 'a2', 'a3', 'a4', 'a5', 'a6');
?>
--EXPECT--
array(7) {
  [0]=>
  string(2) "b0"
  [1]=>
  string(2) "b1"
  [2]=>
  string(2) "b2"
  [3]=>
  string(2) "b3"
  [4]=>
  string(2) "b4"
  [5]=>
  string(2) "b5"
  [6]=>
  string(2) "b6"
}
