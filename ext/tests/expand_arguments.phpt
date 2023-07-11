--TEST--
Check if hook can expand params of built-in function when providing post callback function
--EXTENSIONS--
opentelemetry
--XFAIL--
Using a post callback when expanding params causes segfault
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'array_slice',
    pre: function(null $instance, array $params) {
        return [$params[0], $params[1], 1];
    },
    post: function() {}
);

var_dump(array_slice([1,2,3], 1));
?>
--EXPECT--
array(1) {
  [0]=>
  int(2)
}
