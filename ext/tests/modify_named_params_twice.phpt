--TEST--
Check if pre hook can modify same param via name and index at once
--DESCRIPTION--
Tests that the last entry for the same param (either by name or index) is applied, and that no crashes or memory leaks
are caused by changing the same parameter in two different ways at once.
--EXTENSIONS--
opentelemetry
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'hello',
    function($obj, array $params) {
        return [
          1 => 'twoindex',
          'two' => 'twoname',
          'three' => 'threename',
          2 => 'threeindex',
        ];
    }
);
function hello($one = null, $two = null, $three = null) {
  var_dump(func_get_args());
}

hello('a', 'b', 'c');
?>
--EXPECT--
array(3) {
  [0]=>
  string(1) "a"
  [1]=>
  string(7) "twoname"
  [2]=>
  string(10) "threeindex"
}
