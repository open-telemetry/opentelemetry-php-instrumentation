--TEST--
Check if pre hook can expand and then return $params of internal function
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'array_slice', function($obj, array $params) {
    $params[2] = 1; //only slice 1 value, instead of "remainder"
    return $params;
},
fn() => null);

var_dump(array_slice(['a', 'b', 'c'], 1));
?>
--XFAIL--
Core dump (same issue as in return_expanded_params.phpt)
--EXPECT--
array(1) {
  [0]=>
  string(1) "b"
}
