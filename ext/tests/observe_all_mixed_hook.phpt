--TEST--
Check observeAll and hook working together
--EXTENSIONS--
opentelemetry
--FILE--
<?php
$calls = [];

// Set up specific hook
\OpenTelemetry\Instrumentation\hook(
    null,
    'test_function',
    function($object, $args, $scope, $function) use (&$calls) {
        $calls[] = "specific:" . $function;
    },
    null
);

// Set up wildcard observer
\OpenTelemetry\Instrumentation\observeAll(
    function($object, $args, $scope, $function) use (&$calls) {
        if ($function !== 'var_dump') {
            $calls[] = "wildcard:" . $function;
        }
    },
    null
);

// Test function should trigger both observers
function test_function() {
    return "test";
}

test_function();

\OpenTelemetry\Instrumentation\observeAll();

sort($calls);
var_dump($calls);
?>
--EXPECT--
array(2) {
  [0]=>
  string(22) "specific:test_function"
  [1]=>
  string(22) "wildcard:test_function"
}
