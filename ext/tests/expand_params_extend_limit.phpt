--TEST--
Check if pre hook can expand params of function when that requires extending the stack only until hardcoded limit
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
        return [$params[0], 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u'];
    },
    post: fn() => null
);

function helloWorld($a, $b) {
    var_dump(func_get_args());
}
helloWorld('a');
?>
--EXPECTF--
Warning: helloWorld(): OpenTelemetry: pre hook invalid argument index 18 - exceeds built-in stack extension limit, class=null function=helloWorld in %s
array(18) {
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
  [8]=>
  string(1) "i"
  [9]=>
  string(1) "j"
  [10]=>
  string(1) "k"
  [11]=>
  string(1) "l"
  [12]=>
  string(1) "m"
  [13]=>
  string(1) "n"
  [14]=>
  string(1) "o"
  [15]=>
  string(1) "p"
  [16]=>
  string(1) "q"
  [17]=>
  string(1) "r"
}
