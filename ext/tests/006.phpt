--TEST--
Check if hooks receives arguments and return value
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn() => var_dump(func_get_args()), fn() => var_dump(func_get_args()));

function helloWorld(string $a) {
    return 42;
}

helloWorld('a');
?>
--EXPECTF--
array(8) {
  [0]=>
  NULL
  [1]=>
  array(1) {
    [0]=>
    string(1) "a"
  }
  [2]=>
  NULL
  [3]=>
  string(10) "helloWorld"
  [4]=>
  string(%d) "%s%etests%e006.php"
  [5]=>
  int(4)
  [6]=>
  array(0) {
  }
  [7]=>
  array(0) {
  }
}
array(8) {
  [0]=>
  NULL
  [1]=>
  array(1) {
    [0]=>
    string(1) "a"
  }
  [2]=>
  int(42)
  [3]=>
  NULL
  [4]=>
  NULL
  [5]=>
  string(10) "helloWorld"
  [6]=>
  string(%d) "%s%etests%e006.php"
  [7]=>
  int(4)
}
