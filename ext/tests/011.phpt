--TEST--
Check hooking namespaced function
--EXTENSIONS--
opentelemetry
--FILE--
<?php
namespace Some\Namespace;

//with leading slash
\OpenTelemetry\Instrumentation\hook(
    null,
    '\Some\Namespace\helloWorld',
    function(...$args) {
        var_dump($args);
    }
);

//without leading slash
\OpenTelemetry\Instrumentation\hook(
    null,
    'Some\Namespace\helloWorld',
    function(...$args) {
        var_dump($args);
    }
);
function helloWorld() {
    return 42;
}

\Some\Namespace\helloWorld();
?>
--EXPECTF--
array(8) {
  [0]=>
  NULL
  [1]=>
  array(0) {
  }
  [2]=>
  NULL
  [3]=>
  string(25) "Some\Namespace\helloWorld"
  [4]=>
  string(%d) "%s011.php"
  [5]=>
  int(21)
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
  array(0) {
  }
  [2]=>
  NULL
  [3]=>
  string(25) "Some\Namespace\helloWorld"
  [4]=>
  string(%d) "%s011.php"
  [5]=>
  int(21)
  [6]=>
  array(0) {
  }
  [7]=>
  array(0) {
  }
}
