--TEST--
Check if hooks receives function information
--EXTENSIONS--
opentelemetry
--FILE--
<?php
\OpenTelemetry\Instrumentation\hook(null, 'helloWorld', fn() => var_dump(func_get_args()), fn() => var_dump(func_get_args()));

function helloWorld() {
    var_dump('CALL');
}

helloWorld();
?>
--EXPECTF--
array(6) {
  [0]=>
  NULL
  [1]=>
  array(0) {
  }
  [2]=>
  NULL
  [3]=>
  string(10) "helloWorld"
  [4]=>
  string(%d) "%s/tests/005.php"
  [5]=>
  int(4)
}
string(4) "CALL"
array(8) {
  [0]=>
  NULL
  [1]=>
  array(0) {
  }
  [2]=>
  NULL
  [3]=>
  NULL
  [4]=>
  NULL
  [5]=>
  string(10) "helloWorld"
  [6]=>
  string(%d) "%s/tests/005.php"
  [7]=>
  int(4)
}
