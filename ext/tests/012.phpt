--TEST--
Check hooking namespaced class with and without leading slash
--EXTENSIONS--
opentelemetry
--FILE--
<?php
namespace Some\Namespace;

//with leading slash
\OpenTelemetry\Instrumentation\hook(
    '\Some\Namespace\HelloWorld',
    'sayHello',
    function(...$args) {
        var_dump($args);
    }
);

//without leading slash
\OpenTelemetry\Instrumentation\hook(
    'Some\Namespace\HelloWorld',
    'sayHello',
    function(...$args) {
        var_dump($args);
    }
);

class HelloWorld {
    public static function sayHello() {
        return 42;
    }
}

\Some\Namespace\HelloWorld::sayHello();
?>
--EXPECTF--
array(8) {
  [0]=>
  string(25) "Some\Namespace\HelloWorld"
  [1]=>
  array(0) {
  }
  [2]=>
  string(25) "Some\Namespace\HelloWorld"
  [3]=>
  string(8) "sayHello"
  [4]=>
  string(%d) "%s012.php"
  [5]=>
  int(23)
  [6]=>
  array(0) {
  }
  [7]=>
  array(0) {
  }
}
array(8) {
  [0]=>
  string(25) "Some\Namespace\HelloWorld"
  [1]=>
  array(0) {
  }
  [2]=>
  string(25) "Some\Namespace\HelloWorld"
  [3]=>
  string(8) "sayHello"
  [4]=>
  string(%d) "%s012.php"
  [5]=>
  int(23)
  [6]=>
  array(0) {
  }
  [7]=>
  array(0) {
  }
}
