--TEST--
Check wildcard observer captures all function calls
--EXTENSIONS--
opentelemetry
--FILE--
<?php
$calls = [];

\OpenTelemetry\Instrumentation\observeAll(
    function($object, $args, $scope, $function) use (&$calls) {
        $calls[] = "pre:" . ($scope ?? "global") . "::" . $function;
    },
    function($object, $args, $retval, $exception, $scope, $function) use (&$calls) {
        $calls[] = "post:" . ($scope ?? "global") . "::" . $function;
    }
);

function test_function() {
    return "test";
}

class Demo {
    public static function static_method() {
        return "static";
    }

    public function instance_method() {
        return "instance";
    }
}

// Test regular function
test_function();

// Test static method
Demo::static_method();

// Test instance method
$demo = new Demo();
$demo->instance_method();

\OpenTelemetry\Instrumentation\observeAll();

// Sort and output calls for consistent testing
sort($calls);
foreach($calls as $call) {
    echo $call . "\n";
}
?>
--EXPECT--
post:Demo::instance_method
post:Demo::static_method
post:global::test_function
pre:Demo::instance_method
pre:Demo::static_method
pre:global::test_function
