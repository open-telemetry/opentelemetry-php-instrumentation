--TEST--
Simple: Check wildcard observer captures all function calls
--EXTENSIONS--
opentelemetry
--FILE--
<?php

\OpenTelemetry\Instrumentation\observeAll(
    function($a, $args, $scope, $function) use (&$calls) {
        print "PRE\n";
    },
    function($b, $args, $retval, $exception, $scope, $function) use (&$calls) {
        print "POST\n";
    }
);

function test_function() {
    return "test";
}

// Test regular function
test_function();

?>
--EXPECT--
PRE
POST
