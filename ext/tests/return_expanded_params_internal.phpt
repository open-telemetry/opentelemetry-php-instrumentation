--TEST--
Check if pre hook can expand and then return $params of internal function
--DESCRIPTION--
The existence of a post callback is part of the failure preconditions.
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(
    null,
    'array_slice',
    pre: function($obj, array $params) {
        $params[2] = 1; //only slice 1 value, instead of "remainder"
        return $params;
    },
    post: fn() => null //does not fail without post callback
);

var_dump(array_slice(['a', 'b', 'c'], 1));
?>
--XFAIL--
Core dump (same issue as in return_expanded_params.phpt)
--EXPECT--
array(1) {
  [0]=>
  string(1) "b"
}
