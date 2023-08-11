--TEST--
Check if pre hook can modify named params of function
--EXTENSIONS--
opentelemetry
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'hello',
     function($obj, array $params) {
        return [
          'two' => 'replaced',
        ];
    }
);
function hello($one = null, $two = null, $three = null) {
  var_dump(func_get_args());
}

hello('a', 'b', 'c');
?>
--XFAIL--
Replacing named arguments not implemented
--EXPECT--
array(3) {
  [0]=>
  string(1) "a"
  [1]=>
  string(1) "replaced"
  [2]=>
  string(1) "c"
}
