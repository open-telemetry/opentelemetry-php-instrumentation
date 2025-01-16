--TEST--
Check wildcard observer single instance behavior
--EXTENSIONS--
opentelemetry
--FILE--
<?php
$calls = [];

// First observer should succeed
$result1 = \OpenTelemetry\Instrumentation\observeAll(
    function($object, $args, $scope, $function) use (&$calls) {
        if ($function !== 'var_dump') {  // Ignore var_dump calls
            $calls[] = "observer1:" . $function;
        }
    },
    null
);
var_dump($result1);

// Second observer should fail
$result2 = \OpenTelemetry\Instrumentation\observeAll(
    function($object, $args, $scope, $function) use (&$calls) {
        if ($function !== 'var_dump') {
            $calls[] = "observer2:" . $function;
        }
    },
    null
);
var_dump($result2);

// Test first observer is still working
function test1() { }
test1();
var_dump($calls);

// Reset calls array
$calls = [];

// Disable observer should succeed
$result3 = \OpenTelemetry\Instrumentation\observeAll();
var_dump($result3);

// Test no calls are recorded after disable
function test2() { }
test2();
var_dump($calls);

// New observer after disable should succeed
$result4 = \OpenTelemetry\Instrumentation\observeAll(
    function($object, $args, $scope, $function) use (&$calls) {
        if ($function !== 'var_dump') {
            $calls[] = "observer3:" . $function;
        }
    },
    null
);
var_dump($result4);

// Test new observer is working
function test3() { }
test3();
var_dump($calls);

?>
--EXPECT--
bool(true)
bool(false)
array(1) {
  [0]=>
  string(15) "observer1:test1"
}
bool(true)
array(0) {
}
bool(true)
array(1) {
  [0]=>
  string(15) "observer3:test3"
}
