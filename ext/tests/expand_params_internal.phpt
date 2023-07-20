--TEST--
Check if pre hook can expand params of internal function
--DESCRIPTION--
The segfault is actually during shutdown (garbage collection?). Removing the `post` callback avoids the segfault.
--EXTENSIONS--
opentelemetry
--XFAIL--
Providing a post callback when expanding params of internal function causes segfault
--FILE--
<?php
OpenTelemetry\Instrumentation\hook(
    null,
    'array_slice',
    pre: function(null $instance, array $params) {
        return [$params[0], $params[1], 1];
    },
    post: fn() => null
);

var_dump(array_slice([1,2,3], 1));
var_dump('finished');
?>
--EXPECT--
array(1) {
  [0]=>
  int(2)
}
string(8) "finished"
